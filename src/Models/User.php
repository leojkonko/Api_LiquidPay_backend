<?php

namespace App\Models;

use App\Database;

class User
{
    private $name;
    private $cpf;
    private $email;
    private $password;

    public function __construct($data)
    {
        $this->name = $data['name'];
        $this->cpf = $data['cpf'];
        $this->email = $data['email'];
        $this->password = password_hash($data['password'], PASSWORD_BCRYPT);
    }

    public function save()
    {
        // Código para salvar o usuário no banco de dados
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO users (name, cpf, email, password) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$this->name, $this->cpf, $this->email, $this->password]);
    }
}
