<?php

use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;

// $pdo = new PDO('mysql:host=localhost;dbname=liquidpay', 'root', '');


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
    $auth = authVerified();

    $authData = json_decode($auth, true);

    if (!$authData['authenticated']) {
        echo json_encode(['error' => 'Access denied']);
        return;
    } else {
        echo json_encode($auth);
    }
});


// Rota para listar usuários
handleGetProtectedRequest('/users', function () {
    $auth = authVerified();

    $authData = json_decode($auth, true); // Decodifica a string JSON para um array

    if (!$authData['authenticated']) {
        // Retorna um erro de acesso negado se não autenticado
        echo json_encode(['error' => 'Access denied']);
        return;
    } else {
        echo json_encode($auth);
    }
    $controller = new UserController();
    return $controller->index();
});

// Rota para adicionar créditos
handlePostRequest('/api/add-credits', function ($request) {
    $auth = authVerified();

    $authData = json_decode($auth, true);

    if (!$authData['authenticated']) {
        echo json_encode(['error' => 'Access denied']);
        return;
    } else {
        echo json_encode($auth);
    }

    $controller = new UserController();
    return $controller->addCredits($request);
});


function authVerified()
{
    $authResult = AuthMiddleware::handle(getallheaders());
    if (isset($authResult->iss) && isset($authResult->sub) && isset($authResult->iat) && isset($authResult->exp)) {
        // Acesso autorizado
        $response = [
            'authMessage' => 'Authentication successful',
            'routeMessage' => 'You have accessed a protected route',
            'authenticated' => true
        ];
    } else {
        // Acesso negado
        $response = [
            'authMessage' => $authResult,
            'routeMessage' => 'Access denied',
            'authenticated' => false
        ];
    }
    // return json_encode($authResult->iss);
    return json_encode($response);
}
