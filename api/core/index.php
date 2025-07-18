<?php
include_once '../config/cors.php';
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$settings = [
    'app_name' => 'Raspadinha',
    'app_version' => '1.0.0',
    'maintenance_mode' => false,
    'min_deposit' => 10.00,
    'max_deposit' => 5000.00,
    'min_withdraw' => 20.00,
    'max_withdraw' => 10000.00,
    'pix_enabled' => true,
    'games_enabled' => true,
    'registration_enabled' => true,
    'currency' => 'BRL',
    'currency_symbol' => 'R$',
    'nitro_config' => [
        'enabled' => true,
        'offer_hash' => 'ydpamubeay',
        'product_hash' => '8cru5klgqv'
    ],
    'game_types' => [
        'dinheiro' => [
            'name' => 'Raspadinha Dinheiro',
            'min_bet' => 1.00,
            'max_bet' => 100.00,
            'win_chance' => 0.3,
            'multipliers' => [1.5, 2, 3, 5, 10]
        ],
        'eletronicos' => [
            'name' => 'Eletrônicos',
            'min_bet' => 5.00,
            'max_bet' => 50.00,
            'win_chance' => 0.25,
            'multipliers' => [2, 3, 5, 8]
        ],
        'eletrodomesticos' => [
            'name' => 'Eletrodomésticos',
            'min_bet' => 10.00,
            'max_bet' => 200.00,
            'win_chance' => 0.2,
            'multipliers' => [3, 5, 10, 15]
        ],
        'camisa-de-futebol' => [
            'name' => 'Camisa de Futebol',
            'min_bet' => 2.00,
            'max_bet' => 30.00,
            'win_chance' => 0.35,
            'multipliers' => [1.5, 2, 4, 6]
        ]
    ]
];

http_response_code(200);
echo json_encode($settings);
?>