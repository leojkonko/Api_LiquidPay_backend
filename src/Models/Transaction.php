<?php

namespace App\Models;

use PDO;
use App\Database;

class Transaction
{
    public $id;

    public function __construct($data = null)
    {
    }


    public function registerTransaction($userId, $amount, $status, $transactionId)
    {

        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO transactions (user_id, amount, status, transaction_id) VALUES (:user_id, :amount, :status, :transaction_id)");
        $executed = $stmt->execute([
            'user_id' => $userId,
            'amount' => $amount,
            'status' => $status,
            'transaction_id' => $transactionId
        ]);

        return $executed;
    }
}
