<?php
include_once '../config/cors.php';
include_once '../config/database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

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
    if(!$user->getUserById($user_id)) {
        http_response_code(404);
        echo json_encode(array("message" => "Usuário não encontrado."));
        exit;
    }

    if($user->balance < $data->bet_amount) {
        http_response_code(400);
        echo json_encode(array("message" => "Saldo insuficiente."));
        exit;
    }

    // Debitar valor da aposta
    $user->updateBalance(-$data->bet_amount);

    // Lógica do jogo baseada no tipo
    $game_configs = [
        'dinheiro' => ['win_chance' => 0.3, 'multipliers' => [1.5, 2, 3, 5, 10]],
        'eletronicos' => ['win_chance' => 0.25, 'multipliers' => [2, 3, 5, 8]],
        'eletrodomesticos' => ['win_chance' => 0.2, 'multipliers' => [3, 5, 10, 15]],
        'camisa-de-futebol' => ['win_chance' => 0.35, 'multipliers' => [1.5, 2, 4, 6]]
    ];

    $config = $game_configs[$data->game_type] ?? $game_configs['dinheiro'];
    $win_chance = $config['win_chance'];
    $multipliers = $config['multipliers'];
    
    $is_winner = (rand(1, 100) / 100) <= $win_chance;
    $win_amount = 0;
    $multiplier = 0;
    
    if($is_winner) {
        $multiplier = $multipliers[array_rand($multipliers)];
        $win_amount = $data->bet_amount * $multiplier;
        
        // Creditar prêmio
        $user->updateBalance($win_amount);
    }

    // Salvar jogo no banco
    $result = json_encode([
        'is_winner' => $is_winner,
        'multiplier' => $multiplier,
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
    $user->getUserById($user_id);

    http_response_code(200);
    echo json_encode(array(
        "game_id" => $game_id,
        "is_winner" => $is_winner,
        "win_amount" => $win_amount,
        "multiplier" => $multiplier,
        "new_balance" => floatval($user->balance),
        "message" => $is_winner ? "Parabéns! Você ganhou!" : "Que pena! Tente novamente."
    ));
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Tipo de jogo e valor da aposta são obrigatórios."));
}
?>