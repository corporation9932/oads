<?php
include_once '../config/cors.php';
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

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

// Buscar entregas do usuário
$query = "SELECT d.*, g.game_type, g.bet_amount 
          FROM deliveries d 
          JOIN games g ON d.game_id = g.id 
          WHERE d.user_id = :user_id 
          ORDER BY d.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar dados
foreach($deliveries as &$delivery) {
    $delivery['prize_value'] = floatval($delivery['prize_value']);
    $delivery['bet_amount'] = floatval($delivery['bet_amount']);
}

http_response_code(200);
echo json_encode($deliveries);
?>