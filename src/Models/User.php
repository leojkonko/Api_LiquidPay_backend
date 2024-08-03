<?php

namespace App\Models;

use PDO;
use App\Database;
use Exception;

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
    }
    public function addCredits($userId, $amount)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET balance = balance + :amount WHERE id = :id');
        $executed =  $stmt->execute(['amount' => $amount, 'id' => $userId]);

        return $executed;
    }
    public function changePassword($userId, $currentPassword, $newPassword)
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT password FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return json_encode(['message' => 'Senha atual incorreta']);
        }

        $stmt = $db->prepare("SELECT password_hash FROM password_histories WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $passwordHistories = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($passwordHistories as $oldPasswordHash) {
            if (password_verify($newPassword, $oldPasswordHash)) {
                return json_encode(['message' => 'A nova senha não pode ser a mesma que uma senha anterior']);
            }
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);

        $db->beginTransaction();

        try {
            $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :user_id");
            $stmt->execute([
                'password' => $newPasswordHash,
                'user_id' => $userId
            ]);

            $stmt = $db->prepare("INSERT INTO password_histories (user_id, password_hash) VALUES (:user_id, :password_hash)");
            $stmt->execute([
                'user_id' => $userId,
                'password_hash' => $user['password']
            ]);

            $db->commit();
            return json_encode(['message' => 'Senha alterada com sucesso']);
        } catch (Exception $e) {
            $db->rollBack();
            return json_encode(['message' => 'Erro ao alterar a senha']);
        }
    }
}
