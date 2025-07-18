<?php
include_once '../config/cors.php';
include_once '../config/database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->email) && !empty($data->password)) {
    $user->email = $data->email;
    
    if($user->emailExists()) {
        if(password_verify($data->password, $user->password)) {
            $stats = $user->getStats();
            
            http_response_code(200);
            echo json_encode(array(
                "message" => "Login realizado com sucesso.",
                "user" => array(
                    "id" => $user->id,
                    "username" => $user->username,
                    "email" => $user->email,
                    "phone" => $user->phone,
                    "document" => $user->document,
                    "balance" => floatval($user->balance),
                    "is_admin" => boolval($user->is_admin),
                    "stat" => array(
                        "deposit_sum" => floatval($stats['deposit_sum']),
                        "withdraw_sum" => floatval($stats['withdraw_sum'])
                    )
                ),
                "token" => base64_encode($user->id . ":" . time())
            ));
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Senha incorreta."));
        }
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Usuário não encontrado."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Email e senha são obrigatórios."));
}
?>