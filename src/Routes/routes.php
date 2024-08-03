<?php

use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;

function handlePostRequest($path, $callback)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === $path) {
        $input = json_decode(file_get_contents('php://input'), true);
        echo $callback($input);
        exit;
    }
}

function handleGetProtectedRequest($path, $callback)
{
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === $path) {
        $authResult = AuthMiddleware::handle(getallheaders());
        if (is_array($authResult) && isset($authResult['error'])) {
            echo json_encode($authResult);
        } else {
            echo $callback($_GET);
        }
        exit;
    }
}

handlePostRequest('/register', function ($request) {
    $controller = new UserController();
    return $controller->register($request);
});

handlePostRequest('/login', function ($request) {
    $controller = new UserController();
    return $controller->login($request);
});

handleGetProtectedRequest('/users', function () {
    $auth = authVerified();

    $authData = json_decode($auth, true);

    if (!$authData['authenticated']) {
        echo json_encode(['error' => 'Access denied']);
        return;
    } else {
        echo json_encode($auth);
    }
    $controller = new UserController();
    return $controller->index();
});

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


handleGetProtectedRequest(
    '/statement',
    function ($data) {
        $auth = authVerified();

        $authData = json_decode($auth, true);

        if (!$authData['authenticated']) {
            echo json_encode(['error' => 'Access denied']);
            return;
        } else {
            echo json_encode($auth);
        }

        $user_id = $data['user_id'];
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];

        $controller = new UserController();
        return $controller->getStatement($user_id, $startDate, $endDate);
    }
);


handlePostRequest('/change-password', function ($request) {
    $auth = authVerified();

    $authData = json_decode($auth, true);

    if (!$authData['authenticated']) {
        echo json_encode(['error' => 'Access denied']);
        return;
    } else {
        echo json_encode($auth);
    }
    $controller = new UserController();
    return $controller->changePassword($request);
});

handlePostRequest(
    '/transfer-credits',
    function ($data) {
        $auth = authVerified();

        $authData = json_decode($auth, true);

        if (!$authData['authenticated']) {
            echo json_encode(['error' => 'Access denied']);
            return;
        } else {
            echo json_encode($auth);
        }
        $userId = $data['user_id'] ?? null;
        $cpfRecipient = $data['cpf_recipient'] ?? null;
        $amount = $data['amount'] ?? null;

        $controller = new UserController();
        return $controller->transferCredits($userId, $cpfRecipient, $amount);
    }
);



function authVerified()
{
    $authResult = AuthMiddleware::handle(getallheaders());
    if (isset($authResult->iss) && isset($authResult->sub) && isset($authResult->iat) && isset($authResult->exp)) {
        $response = [
            'authMessage' => 'Authentication successful',
            'routeMessage' => 'You have accessed a protected route',
            'authenticated' => true
        ];
    } else {
        $response = [
            'authMessage' => $authResult,
            'routeMessage' => 'Access denied',
            'authenticated' => false
        ];
    }
    return json_encode($response);
}
