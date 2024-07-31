<?php

use App\Controllers\UserController;

// Função para lidar com solicitações POST
function handlePostRequest($path, $callback)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === $path) {
        $input = json_decode(file_get_contents('php://input'), true);
        echo $callback($input);
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
