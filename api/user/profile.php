<?php
include_once '../config/cors.php';
include_once '../config/database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

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

// Buscar dados do usuário
$query = "SELECT id, username, email, phone, document, balance, created_at, is_admin FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(":id", $user_id);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$userData) {
    http_response_code(404);
    echo json_encode(array("message" => "Usuário não encontrado."));
    exit;
}

// Buscar estatísticas
$user->id = $user_id;
$stats = $user->getStats();

// Buscar últimas transações
$query = "SELECT * FROM transactions WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar últimos jogos
$query = "SELECT * FROM games WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$recent_games = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [
    'user' => [
        'id' => intval($userData['id']),
        'username' => $userData['username'],
        'email' => $userData['email'],
        'phone' => $userData['phone'],
        'document' => $userData['document'],
        'balance' => floatval($userData['balance']),
        'is_admin' => boolval($userData['is_admin']),
        'created_at' => $userData['created_at']
    ],
    'stats' => [
        'deposit_sum' => floatval($stats['deposit_sum']),
        'withdraw_sum' => floatval($stats['withdraw_sum']),
        'total_games' => count($recent_games),
        'total_wins' => array_reduce($recent_games, function($carry, $game) {
            return $carry + ($game['win_amount'] > 0 ? 1 : 0);
        }, 0)
    ],
    'recent_transactions' => $recent_transactions,
    'recent_games' => $recent_games
];

http_response_code(200);
echo json_encode($response);
?>