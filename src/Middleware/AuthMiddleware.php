<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Services\Response;

class AuthMiddleware
{
    public static function handle($headers)
    {
        if (!isset($headers['Authorization'])) {
            return Response::json(['error' => 'Authorization header not found'], 401);
        }

        $authHeader = $headers['Authorization'];
        list($jwt) = sscanf($authHeader, 'Bearer %s');

        if (!$jwt) {
            return Response::json(['error' => 'Invalid token format'], 401);
        }

        try {
            $decoded = JWT::decode($jwt, new Key('your-secret-key', 'HS256'));
            return $decoded;
        } catch (\Exception $e) {
            return Response::json(['error' => 'Invalid token: ' . $e->getMessage()], 401);
        }
    }
}
