<?php
include_once '../config/cors.php';
include_once '../config/database.php';
include_once '../models/Transaction.php';

$database = new Database();
$db = $database->getConnection();
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

$transactions = $transaction->getUserTransactions($user_id);

http_response_code(200);
echo json_encode($transactions);
?>