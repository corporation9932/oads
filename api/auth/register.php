<?php
include_once '../config/cors.php';
include_once '../config/database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->username) && !empty($data->email) && !empty($data->password) && 
   !empty($data->phone) && !empty($data->document)) {

    $user->username = $data->username;
    $user->email = $data->email;
    $user->password = $data->password;
    $user->phone = $data->phone;
    $user->document = $data->document;

    // Verificar se email já existe
    if($user->emailExists()) {
        http_response_code(400);
        echo json_encode(array("message" => "Email já está em uso."));
        exit;
    }

    if($user->create()) {
        http_response_code(201);
        echo json_encode(array(
            "message" => "Usuário criado com sucesso.",
            "user" => array(
                "id" => $user->id,
                "username" => $user->username,
                "email" => $user->email,
                "phone" => $user->phone,
                "document" => $user->document,
                "balance" => 0
            )
        ));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Não foi possível criar o usuário."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Dados incompletos."));
}
?>