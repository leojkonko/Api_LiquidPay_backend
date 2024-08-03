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

    public function getTransactionsByPeriod($userId, $startDate, $endDate)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM transactions WHERE user_id = :user_id AND created_at BETWEEN :start_date AND :end_date");

        // Executa a consulta passando os parâmetros nomeados
        $executed = $stmt->execute([
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        // Retorna os resultados da consulta
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
