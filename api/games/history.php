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

// Buscar histórico de jogos do usuário
$query = "SELECT * FROM games WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 50";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar resultados
foreach($games as &$game) {
    $game['result'] = json_decode($game['result'], true);
    $game['bet_amount'] = floatval($game['bet_amount']);
    $game['win_amount'] = floatval($game['win_amount']);
}

http_response_code(200);
echo json_encode($games);
?>