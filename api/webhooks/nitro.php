<?php
include_once '../config/database.php';
include_once '../models/User.php';
include_once '../models/Transaction.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$transaction = new Transaction($db);

// Receber webhook da Nitro
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log do webhook para debug
file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . " - " . $input . "\n", FILE_APPEND);

if($data && isset($data['id']) && isset($data['status'])) {
    // Buscar transação pelo external_id
    $query = "SELECT * FROM transactions WHERE external_id = :external_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":external_id", $data['id']);
    $stmt->execute();
    $transactionData = $stmt->fetch(PDO::FETCH_ASSOC);

    if($transactionData) {
        $transaction->id = $transactionData['id'];
        
        // Atualizar status baseado no webhook
        $newStatus = 'pending';
        switch($data['status']) {
            case 'approved':
            case 'paid':
                $newStatus = 'completed';
                // Creditar saldo se for depósito aprovado
                if($transactionData['type'] == 'deposit') {
                    $user->id = $transactionData['user_id'];
                    $user->updateBalance($transactionData['amount']);
                }
                break;
            case 'cancelled':
            case 'refunded':
                $newStatus = 'cancelled';
                break;
            case 'failed':
                $newStatus = 'failed';
                break;
        }
        
        $transaction->updateStatus($newStatus);
        
        http_response_code(200);
        echo json_encode(array("message" => "Webhook processado com sucesso."));
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Transação não encontrada."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Dados do webhook inválidos."));
}
?>