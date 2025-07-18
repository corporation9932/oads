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

$data = json_decode(file_get_contents("php://input"));

$user->id = $user_id;

// Atualizar campos específicos
if(isset($data->username)) {
    $user->username = $data->username;
}
if(isset($data->phone)) {
    $user->phone = $data->phone;
}
if(isset($data->document)) {
    $user->document = $data->document;
}

if($user->update()) {
    http_response_code(200);
    echo json_encode(array("message" => "Usuário atualizado com sucesso."));
} else {
    http_response_code(503);
    echo json_encode(array("message" => "Não foi possível atualizar o usuário."));
}
?>