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

// Estatísticas do dashboard
$stats = [];

// Total de usuários
$query = "SELECT COUNT(*) as total_users FROM users";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

// Total de transações
$query = "SELECT COUNT(*) as total_transactions FROM transactions";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_transactions'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_transactions'];

// Total depositado
$query = "SELECT COALESCE(SUM(amount), 0) as total_deposits FROM transactions WHERE type = 'deposit' AND status = 'completed'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_deposits'] = floatval($stmt->fetch(PDO::FETCH_ASSOC)['total_deposits']);

// Total sacado
$query = "SELECT COALESCE(SUM(amount), 0) as total_withdraws FROM transactions WHERE type = 'withdraw' AND status = 'completed'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_withdraws'] = floatval($stmt->fetch(PDO::FETCH_ASSOC)['total_withdraws']);

// Total de jogos
$query = "SELECT COUNT(*) as total_games FROM games";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_games'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_games'];

// Total ganho em jogos
$query = "SELECT COALESCE(SUM(win_amount), 0) as total_winnings FROM games";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_winnings'] = floatval($stmt->fetch(PDO::FETCH_ASSOC)['total_winnings']);

http_response_code(200);
echo json_encode($stats);
?>