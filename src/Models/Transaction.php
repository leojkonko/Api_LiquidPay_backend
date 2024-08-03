<?php

namespace App\Models;

use PDO;
use App\Database;
use Exception;

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

    public function transferBalance($senderId, $receiverId, $amount)
    {
        $db = Database::getInstance();

        // Inicia a transação
        $db->beginTransaction();

        try {
            // Atualiza o saldo do remetente
            $stmt = $db->prepare("UPDATE users SET balance = balance - :amount WHERE id = :user_id");
            $executed =  $stmt->execute([
                'amount' => $amount,
                'user_id' => $senderId
            ]);
            echo json_encode(['message' => 'Atualizado saldo do remetente: ' . $executed]);

            // Atualiza o saldo do receptor
            $stmt = $db->prepare("UPDATE users SET balance = balance + :amount WHERE id = :user_id");
            $executed2 = $stmt->execute([
                'amount' => $amount,
                'user_id' => $receiverId
            ]);
            echo json_encode(['message' => 'Atualizado saldo do receptor: ' . $executed2]);

            // // Registra a transação de débito para o remetente  
            // $stmt = $db->prepare("INSERT INTO transactions (user_id, amount, type, status, transaction_id) VALUES (:user_id, :amount, :type, :status, :transaction_id)");
            // $executed3 = $stmt->execute([
            //     'user_id' => $senderId,
            //     'amount' => $amount,
            //     'type' => 'debit',
            //     'status' => 'completed',
            //     'transaction_id' => uniqid()
            // ]);
            // echo json_encode(['message' => 'registra transação para o remetente: ' . $executed3]);

            // // Registra a transação de crédito para o receptor
            // $stmt = $db->prepare("INSERT INTO transactions (user_id, amount, type, status, transaction_id) VALUES (:user_id, :amount, :type, :status, :transaction_id)");
            // $executed4 = $stmt->execute([
            //     'user_id' => $receiverId,
            //     'amount' => $amount,
            //     'type' => 'credit',
            //     'status' => 'completed',
            //     'transaction_id' => uniqid()
            // ]);
            // echo json_encode(['message' => 'Atualizado saldo do receptor: ' . $executed4]);

            // Registra a transferência na tabela transfers
            $stmt = $db->prepare("INSERT INTO transfers (sender_id, receiver_id, amount) VALUES (:sender_id, :receiver_id, :amount)");
            $executed5 = $stmt->execute([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'amount' => $amount
            ]);
            echo json_encode(['message' => 'Registro da tranferência: ' . $executed5]);

            // Commit da transação
            $db->commit();

            return true;
        } catch (Exception $e) {
            // Rollback da transação em caso de erro
            $db->rollBack();
            return false;
        }
    }
}
