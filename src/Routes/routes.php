<?php

use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;

// Função para lidar com solicitações POST
function handlePostRequest($path, $callback)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === $path) {
        $input = json_decode(file_get_contents('php://input'), true);
        echo $callback($input);
        exit;
    }
}

// Função para lidar com solicitações GET protegidas
function handleGetProtectedRequest($path, $callback)
{
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === $path) {
        $authResult = AuthMiddleware::handle(getallheaders());
        if (is_array($authResult) && isset($authResult['error'])) {
            echo $authResult;
        } else {
            echo $callback();
        }
        exit;
    }
}

// Rota para cadastro de usuário
handlePostRequest('/register', function ($request) {
    $controller = new UserController();
    return $controller->register($request);
});

// Rota para login de usuário
handlePostRequest('/login', function ($request) {
    $controller = new UserController();
    return $controller->login($request);
});

// Rota protegida de exemplo
handleGetProtectedRequest('/protected', function () {
    $authResult = AuthMiddleware::handle(getallheaders());
    if (isset($authResult->iss) && isset($authResult->sub) && isset($authResult->iat) && isset($authResult->exp)) {
        // Acesso autorizado
        $response = [
            'authMessage' => 'Authentication successful',
            'routeMessage' => 'You have accessed a protected route'
        ];
    } else {
        // Acesso negado
        $response = [
            'authMessage' => $authResult,
            'routeMessage' => 'Access denied'
        ];
    }
    // return json_encode($authResult->iss);
    return json_encode($response);
});


// Rota para listar usuários
handleGetProtectedRequest('/users', function () {
    $controller = new UserController();
    return $controller->index();
});
