<?php

namespace App\Services;

class Response
{
    public static function json($data, $status = 200)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        return json_encode($data);
    }
}
