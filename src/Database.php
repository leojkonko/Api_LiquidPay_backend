<?php

namespace App;

use PDO;

class Database
{
    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=localhost;dbname=liquidpay';
            $username = 'root';
            $password = '';
            self::$instance = new PDO($dsn, $username, $password);
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$instance;
    }
}
