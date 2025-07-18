<?php
include_once '../config/cors.php';
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Verificar token de autorização e se é admin
$headers = getallheaders();
if(!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(array("message" => "Token de acesso requerido."));
    exit;
}

$token = str_replace('Bearer ', '', $headers['Authorization']);
$decoded = base64_decode($token);
$user_id = explode(':', $decoded)[0];

// Verificar se é admin
$query = "SELECT is_admin FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(":id", $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user || !$user['is_admin']) {
    http_response_code(403);
    echo json_encode(array("message" => "Acesso negado."));
    exit;
}

// Listar todas as transações com dados do usuário
$query = "SELECT t.*, u.username, u.email 
          FROM transactions t 
          JOIN users u ON t.user_id = u.id 
          ORDER BY t.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

http_response_code(200);
echo json_encode($transactions);
?>