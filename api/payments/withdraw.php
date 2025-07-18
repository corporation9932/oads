<?php
include_once '../config/cors.php';
include_once '../config/database.php';
include_once '../models/User.php';
include_once '../models/Transaction.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$transaction = new Transaction($db);

// Verificar token de autorização
$headers = getallheaders();
if(!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(array("message" => "Token de acesso requerido."));
    exit;
}

$token = str_replace('Bearer ', '', $headers['Authorization']);
$decoded = base64_decode($token);
$user_id = explode(':', $decoded)[0];

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->amount) && !empty($data->pix_key)) {
    // Buscar dados do usuário
    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $user_id);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$userData) {
        http_response_code(404);
        echo json_encode(array("message" => "Usuário não encontrado."));
        exit;
    }

    // Verificar se tem saldo suficiente
    if($userData['balance'] < $data->amount) {
        http_response_code(400);
        echo json_encode(array("message" => "Saldo insuficiente."));
        exit;
    }

    // Criar transação de saque
    $transaction->user_id = $user_id;
    $transaction->type = 'withdraw';
    $transaction->amount = $data->amount;
    $transaction->status = 'pending';
    $transaction->payment_method = 'pix';
    $transaction->external_id = 'withdraw_' . time();

    if($transaction->create()) {
        // Debitar do saldo
        $user->id = $user_id;
        $user->updateBalance(-$data->amount);

        http_response_code(200);
        echo json_encode(array(
            "message" => "Saque solicitado com sucesso.",
            "transaction_id" => $transaction->id,
            "status" => "pending"
        ));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Erro ao processar saque."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Valor e chave PIX são obrigatórios."));
}
?>