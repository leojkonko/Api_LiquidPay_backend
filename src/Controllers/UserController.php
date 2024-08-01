<?php

namespace App\Controllers;

use App\Models\User;
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

        // Aqui você pode adicionar a lógica para adicionar créditos
        // Envio para a LiquidBank, atualização de saldo, etc.
        // Envio para a LiquidBank
        $response = $this->sendToLiquidBank([
            'type' => $type,
            'number' => $number,
            'brand' => $brand,
            'valid' => $valid,
            'cvv' => $cvv,
            'amount' => $amount
        ]);

        // if ($response['status'] == 'approved') {
        //     echo json_encode(['message' => 'Créditos com sucesso']);
        // } else {
        //     echo json_encode(['message' => 'Créditos nop com sucesso']);
        // }

        return json_encode(['message return api' => $response]);
        // http_response_code(200);
        echo json_encode(['message' => 'Créditos adicionados com sucesso']);
    }

    private function sendToLiquidBank($data)
    {
        // return json_encode(['message' => 'Créditos adicionados com sucessodsfsd']);
        // URL do endpoint da LiquidBank
        $url = 'https://www.liquidworks.com.br/liquidbank/authorize';
        $headers = [
            'Content-Type: application/json',
            // 'Authorization: Bearer your_api_key_here' // Substitua pelo seu token de autenticação real
        ];

        // Dados do payload
        $payload = json_encode([
            'type' => $data['type'], // 'debit' ou 'credit'
            'number' => $data['number'], // Número do cartão
            'brand' => $data['brand'], // 'visa' ou 'master'
            'valid' => $data['valid'], // Validade no formato MM/YY
            'cvv' => $data['cvv'], // Código de segurança do cartão
            'amount' => $data['amount'] // Valor da transação
        ]);

        // Inicializando cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Executando e obtendo a resposta
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Fechando a conexão cURL
        curl_close($ch);
        $apiResponse = json_decode($response, true);
        return json_encode($apiResponse);
        // Verificando a resposta da API
        if ($httpCode == 200) {
            // Supondo que a resposta da API seja JSON
            $apiResponse = json_decode($response, true);

            // Supondo que a API retorne um campo 'status' que pode ser 'approved' ou 'denied'
            return json_encode($apiResponse);
            if (isset($apiResponse['status']) && $apiResponse['status'] == 'approved') {
                // return ['status' => 'approved'];
                return json_encode($apiResponse);
            } else {
                // return ['status' => 'denied'];
                return json_encode($apiResponse);
            }
        } else {
            // Em caso de falha na comunicação com a API
            return ['status' => 'denieddd'];
        }
    }

    private function logTransaction($userId, $amount, $status)
    {
        // Registro da transação para auditoria
        // Isso pode incluir salvar no banco de dados ou em logs
    }
}
