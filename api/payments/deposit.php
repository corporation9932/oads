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

if(!empty($data->amount)) {
    // Buscar dados do usuário
    if(!$user->getUserById($user_id)) {
        http_response_code(404);
        echo json_encode(array("message" => "Usuário não encontrado."));
        exit;
    }

    // Configuração da API Nitro
    $apiToken = 'AJTQzn8xWuYXrjNu5XWajspWi8i6sd9XzkgEViaDpkIrwyKRKCkC1fHCFY1P';
    $endpoint = 'https://api.nitropagamentos.com/api/public/v1/transactions?api_token=' . $apiToken;

    $amount = intval($data->amount * 100); // converter para centavos

    $nitroData = [
        "amount" => $amount,
        "offer_hash" => "ydpamubeay",
        "payment_method" => "pix",
        "customer" => [
            "name" => $user->username,
            "email" => $user->email,
            "phone_number" => preg_replace('/\D/', '', $user->phone),
            "document" => preg_replace('/\D/', '', $user->document),
            "street_name" => "",
            "number" => "",
            "complement" => "",
            "neighborhood" => "",
            "city" => "",
            "state" => "",
            "zip_code" => ""
        ],
        "cart" => [
            [
                "product_hash" => "8cru5klgqv",
                "title" => "Depósito Raspadinha",
                "cover" => null,
                "price" => $amount,
                "quantity" => 1,
                "operation_type" => 1,
                "tangible" => false
            ]
        ],
        "installments" => 1,
        "expire_in_days" => 1,
        "postback_url" => ""
    ];

    // Chamada para API Nitro
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($nitroData));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if($httpCode == 200 || $httpCode == 201) {
        $nitroResponse = json_decode($response, true);
        
        // Criar transação no banco
        $transaction->user_id = $user_id;
        $transaction->type = 'deposit';
        $transaction->amount = $data->amount;
        $transaction->status = 'pending';
        $transaction->payment_method = 'pix';
        $transaction->external_id = $nitroResponse['id'] ?? null;
        
        if($transaction->create()) {
            http_response_code(200);
            echo $response;
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Erro ao salvar transação."));
        }
    } else {
        http_response_code($httpCode);
        echo $response;
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Valor é obrigatório."));
}
?>