<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Services\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;

class UserController
{
    // private $pdo;

    // public function __construct(PDO $pdo)
    // {
    //     $this->pdo = $pdo;
    // }

    public function register($request)
    {
        // Validação dos dados de entrada
        if (empty($request['name']) || empty($request['cpf']) || empty($request['email']) || empty($request['password'])) {
            return Response::json(['error' => 'All fields are required'], 400);
        }

        // Criação do usuário
        $user = new User($request);
        if ($user->save()) {
            return Response::json(['message' => 'User registered successfully'], 201);
        } else {
            return Response::json(['error' => 'Failed to register user'], 500);
        }
    }

    public function login($request)
    {
        // Validação dos dados de entrada
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

        // Verificação do usuário
        if ($user && password_verify($request['password'], $user->password)) {
            // Geração do token JWT
            $payload = [
                'iss' => "your-domain.com",  // Emissor do token
                'sub' => $user->id,          // Identificador do usuário
                'iat' => time(),             // Hora de emissão
                'exp' => time() + 3600       // Expiração (1 hora)
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
        // Verifica se os campos obrigatórios estão presentes
        // $requiredFields = ['card_type', 'card_number', 'card_brand', 'card_valid', 'card_cvv', 'amount'];
        // foreach ($requiredFields as $field) {
        //     if (!isset($request[$field])) {
        //         http_response_code(400);
        //         echo json_encode(['message' => 'Dados inválidos']);
        //         return;
        //     }
        // }

        $type = $request['card_type'];
        $number = $request['card_number'];
        $brand = $request['card_brand'];
        $valid = $request['card_valid'];
        $cvv = $request['card_cvv'];
        $amount = $request['amount'];
        $userId = $request['user_id'];

        // 1. Validação do tipo de cartão
        if (!in_array(
            $type,
            ['debit', 'credit']
        )) {
            http_response_code(400);
            echo json_encode(['message' => 'Tipo de cartão inválido']);
            return;
        }

        // 2. Validação do número do cartão
        if (!preg_match('/^\d{16}$/', $number)) {
            http_response_code(400);
            echo json_encode(['message' => 'Número de cartão inválido']);
            return;
        }

        // 3. Validação da bandeira do cartão
        if (!in_array(strtolower($brand), ['visa', 'master'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Bandeira do cartão inválida']);
            return;
        }

        // 4. Validação da data de validade
        if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $valid)) {
            http_response_code(400);
            echo json_encode(['message' => 'Data de validade inválida']);
            return;
        }

        // 5. Validação do valor
        if (
            !is_numeric($amount) || $amount % 2 != 0
        ) {
            http_response_code(400);
            echo json_encode(['message' => 'Valor inválido para aprovação']);
            return;
        }
        // 5. Validação do id
        if (!isset($userId)) {
            http_response_code(400);
            return json_encode(['message' => 'id user not found']);
        }

        // Envio para a LiquidBank
        $response = $this->sendToLiquidBank([
            'type' => $type,
            'number' => $number,
            'brand' => $brand,
            'valid' => $valid,
            'cvv' => $cvv,
            'amount' => $amount
        ]);

        //encontrar user correto
        $user = User::find($userId);
        $userId_to_number = $user->id;
        //registro da transação
        // Registro da transação (independentemente do status)
        $transactionId = $response['transaction']['id'];
        $transactionStatus = $response['transaction']['statusCode'];
        $transaction = new Transaction();
        $response_transaction = $transaction->registerTransaction($userId_to_number, $amount, $transactionStatus, $transactionId);
        if ($response_transaction) {
            echo json_encode(['message' => "registro cadastrado com sucesso em transações"]);
        } else {
            echo json_encode(['message' => "erro register cadaster"]);
        }

        // return json_encode(['message111' => $response]);
        if ($response['transaction']['statusCode'] == 1) {

            //atualiza saldo do usuario
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
}
