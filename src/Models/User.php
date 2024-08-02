<?php

namespace App\Models;

use PDO;
use App\Database;

class User
{
    public $id;
    public $name;
    public $cpf;
    public $email;
    public $password;
    public $balance;

    public function __construct($data = null)
    {
        if ($data) {
            $this->name = $data['name'];
            $this->cpf = $data['cpf'];
            $this->email = $data['email'];
            $this->password = password_hash($data['password'], PASSWORD_BCRYPT);
            $this->balance = $data['balance'] ?? 0;
        }
    }

    public function save()
    {
        // Código para salvar o usuário no banco de dados
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO users (name, cpf, email, password) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$this->name, $this->cpf, $this->email, $this->password]);
    }

    public static function findByEmail($email)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        return $stmt->fetch();
    }
    public static function findByCpf($cpf)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE cpf = ?");
        $stmt->execute([$cpf]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        return $stmt->fetch();
    }
    public static function all()
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users");
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        return $stmt->fetchAll();
    }
    public static function find($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        return $stmt->fetch();
        // if ($data) {
        //     return new User($data);
        // } else {
        //     return null;
        // }
    }
    public function addCredits($userId, $amount)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET balance = balance + :amount WHERE id = :id');
        $executed =  $stmt->execute(['amount' => $amount, 'id' => $userId]);
        // $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        // return $stmt->fetch();

        return $executed;
    }
}
