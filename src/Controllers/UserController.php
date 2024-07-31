<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserController
{
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
}
