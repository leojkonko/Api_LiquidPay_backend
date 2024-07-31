<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\Response;

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
}
