<?php
include_once '../config/cors.php';
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Verificar token de autorización
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

if(!empty($data->game_type) && !empty($data->bet_amount)) {
    // Verificar saldo do usuário
    $query = "SELECT balance FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$user || $user['balance'] < $data->bet_amount) {
        http_response_code(400);
        echo json_encode(array("message" => "Saldo insuficiente."));
        exit;
    }

    // Debitar valor da aposta
    $query = "UPDATE users SET balance = balance - :amount WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":amount", $data->bet_amount);
    $stmt->bindParam(":id", $user_id);
    $stmt->execute();

    // Lógica do jogo (exemplo simples)
    $win_chance = 0.3; // 30% de chance de ganhar
    $is_winner = (rand(1, 100) / 100) <= $win_chance;
    
    $multipliers = [1.5, 2, 3, 5, 10];
    $win_amount = 0;
    
    if($is_winner) {
        $multiplier = $multipliers[array_rand($multipliers)];
        $win_amount = $data->bet_amount * $multiplier;
        
        // Creditar prêmio
        $query = "UPDATE users SET balance = balance + :amount WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":amount", $win_amount);
        $stmt->bindParam(":id", $user_id);
        $stmt->execute();
    }

    // Salvar jogo no banco
    $result = json_encode([
        'is_winner' => $is_winner,
        'multiplier' => $is_winner ? $multiplier : 0,
        'win_amount' => $win_amount
    ]);

    $query = "INSERT INTO games (user_id, game_type, bet_amount, win_amount, status, result, created_at) 
              VALUES (:user_id, :game_type, :bet_amount, :win_amount, 'completed', :result, NOW())";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":game_type", $data->game_type);
    $stmt->bindParam(":bet_amount", $data->bet_amount);
    $stmt->bindParam(":win_amount", $win_amount);
    $stmt->bindParam(":result", $result);
    $stmt->execute();

    $game_id = $db->lastInsertId();

    // Buscar saldo atualizado
    $query = "SELECT balance FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $user_id);
    $stmt->execute();
    $updated_user = $stmt->fetch(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(array(
        "game_id" => $game_id,
        "is_winner" => $is_winner,
        "win_amount" => $win_amount,
        "multiplier" => $is_winner ? $multiplier : 0,
        "new_balance" => floatval($updated_user['balance']),
        "message" => $is_winner ? "Parabéns! Você ganhou!" : "Que pena! Tente novamente."
    ));
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Tipo de jogo e valor da aposta são obrigatórios."));
}
?>