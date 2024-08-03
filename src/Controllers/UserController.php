<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Services\Response;
use DateTime;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Message;

class UserController
{
    public function register($request)
    {
        if (empty($request['name']) || empty($request['cpf']) || empty($request['email']) || empty($request['password'])) {
            return Response::json(['error' => 'All fields are required'], 400);
        }

        $user = new User($request);
        if ($user->save()) {
            return Response::json(['message' => 'User registered successfully'], 201);
        } else {
            return Response::json(['error' => 'Failed to register user'], 500);
        }
    }

    public function login($request)
    {
        if (empty($request['email']) && empty($request['cpf'])) {
            return Response::json(['error' => 'Email or cpf are required'], 400);
        }

        if (!empty($request['email'])) {
            $user = User::findByEmail($request['email']);
        } else if (!empty($request['cpf'])) {
            $user = User::findByCpf($request['cpf']);
        }

        if (empty($request['password'])) {
            return Response::json(['error' => 'Password are required'], 400);
        }

        if ($user && password_verify($request['password'], $user->password)) {
            $payload = [
                'iss' => "your-domain.com",
                'sub' => $user->id,
                'iat' => time(),
                'exp' => time() + 3600
            ];
            $jwt = JWT::encode($payload, 'your-secret-key', 'HS256');
            return Response::json(['token' => $jwt], 200);
        } else {
            return Response::json(['error' => 'Invalid credentials'], 401);
        }
    }

    public function index()
    {
        $users = User::all();
        return Response::json($users);
    }

    public function addCredits($request)
    {
        $type = $request['card_type'];
        $number = $request['card_number'];
        $brand = $request['card_brand'];
        $valid = $request['card_valid'];
        $cvv = $request['card_cvv'];
        $amount = $request['amount'];
        $userId = $request['user_id'];

        if (!in_array(
            $type,
            ['debit', 'credit']
        )) {
            http_response_code(400);
            echo json_encode(['message' => 'Tipo de cartão inválido']);
            return;
        }

        if (!preg_match('/^\d{16}$/', $number)) {
            http_response_code(400);
            echo json_encode(['message' => 'Número de cartão inválido']);
            return;
        }

        if (!in_array(strtolower($brand), ['visa', 'master'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Bandeira do cartão inválida']);
            return;
        }

        if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $valid)) {
            http_response_code(400);
            echo json_encode(['message' => 'Data de validade inválida']);
            return;
        }

        if (
            !is_numeric($amount) || $amount % 2 != 0
        ) {
            http_response_code(400);
            echo json_encode(['message' => 'Valor inválido para aprovação']);
            return;
        }

        if (!isset($userId)) {
            http_response_code(400);
            return json_encode(['message' => 'id user not found']);
        }

        $response = $this->sendToLiquidBank([
            'type' => $type,
            'number' => $number,
            'brand' => $brand,
            'valid' => $valid,
            'cvv' => $cvv,
            'amount' => $amount
        ]);

        $user = User::find($userId);
        $userId_to_number = $user->id;

        $transactionId = $response['transaction']['id'];
        $transactionStatus = $response['transaction']['statusCode'];
        $transaction = new Transaction();
        $response_transaction = $transaction->registerTransaction($userId_to_number, $amount, $transactionStatus, $transactionId);
        if ($response_transaction) {
            echo json_encode(['message' => "registro cadastrado com sucesso em transações"]);
        } else {
            echo json_encode(['message' => "erro register cadaster"]);
        };
        if ($response['transaction']['statusCode'] == 1) {

            if ($user && $userId_to_number) {
                $userModel = new User();
                $response = $userModel->addCredits($userId_to_number, $amount);
                if ($response) {
                    http_response_code(200);
                    return json_encode(['message4' => 'Créditos adicionados com sucesso no user id: ' . $userId_to_number]);
                } else {
                    http_response_code(404);
                    return json_encode(['message4' => 'User not found - database error']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Usuário não encontrado']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Transação negada pela LiquidBank', 'statusMessage' => $response['transaction']['statusMessage']]);
        }
    }

    private function sendToLiquidBank($data)
    {
        $client = new Client();
        $response = $client->post('https://www.liquidworks.com.br/liquidbank/authorize', [
            'json' => $data
        ]);

        $body = json_decode($response->getBody(), true);

        return $body;
    }

    public function getStatement(
        $user_id,
        $startDate,
        $endDate
    ) {
        if (
            !$user_id || !$startDate || !$endDate || !isValidDate($startDate) || !isValidDate($endDate)
        ) {
            http_response_code(400);
            return json_encode(['message' => 'Parâmetros de data inválidos. Envie start_date e end_date no formato YYYY-MM-DD']);
        }

        $transactionModel = new Transaction();
        $transactions = $transactionModel->getTransactionsByPeriod($user_id, $startDate, $endDate);

        if ($transactions) {
            http_response_code(200);
            return json_encode($transactions);
        } else {
            http_response_code(404);
            return json_encode(['message' => 'Nenhuma transação encontrada para o período especificado']);
        }
    }

    public function changePassword($request)
    {
        $userId = $request['user_id'];
        $current_password = $request['current_password'];
        $new_password = $request['new_password'];

        if (
            !$userId || !$current_password || !$new_password
        ) {
            http_response_code(400);
            return json_encode(['message' => 'Parâmetros de data inválidos.']);
        }
        $userModel = new User();

        return $userModel->changePassword($userId, $current_password, $new_password);
    }

    public function transferCredits($userId, $cpfRecipient, $amount)
    {
        if (!$userId || !$cpfRecipient || !$amount || !is_numeric($amount) || $amount <= 0) {
            http_response_code(400);
            return json_encode(['message' => 'Parâmetros inválidos. Envie user_id, cpf_recipient e amount válidos.']);
        }

        $recipient = User::findByCpf($cpfRecipient);
        if (!$recipient) {
            http_response_code(404);
            return json_encode(['message' => 'Usuário destinatário não encontrado']);
        }

        $sender = User::find($userId);
        if (!$sender) {
            http_response_code(404);
            return json_encode(['message' => 'Usuário logado não encontrado']);
        }

        if ($sender->balance < $amount) {
            http_response_code(400);
            return json_encode(['message' => 'Saldo insuficiente para a transferência']);
        }

        $transactionModel = new Transaction();
        $result = $transactionModel->transferBalance($sender->id, $recipient->id, $amount);

        if ($result) {
            http_response_code(200);
            return json_encode(['message' => 'Transferência realizada com sucesso']);
        } else {
            http_response_code(500);
            return json_encode(['message' => 'Erro ao realizar a transferência']);
        }
    }
}

function isValidDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
