<?php
include_once '../config/cors.php';

$game_types = [
    [
        'id' => 'dinheiro',
        'name' => 'Raspadinha Dinheiro',
        'description' => 'Ganhe dinheiro real raspando!',
        'min_bet' => 1.00,
        'max_bet' => 100.00,
        'win_chance' => 0.3,
        'multipliers' => [1.5, 2, 3, 5, 10],
        'image' => '/assets/scratch.png',
        'active' => true
    ],
    [
        'id' => 'eletronicos',
        'name' => 'Eletrônicos',
        'description' => 'Ganhe smartphones, tablets e mais!',
        'min_bet' => 5.00,
        'max_bet' => 50.00,
        'win_chance' => 0.25,
        'multipliers' => [2, 3, 5, 8],
        'image' => '/assets/scratch.png',
        'active' => true
    ],
    [
        'id' => 'eletrodomesticos',
        'name' => 'Eletrodomésticos',
        'description' => 'Geladeiras, fogões e muito mais!',
        'min_bet' => 10.00,
        'max_bet' => 200.00,
        'win_chance' => 0.2,
        'multipliers' => [3, 5, 10, 15],
        'image' => '/assets/scratch.png',
        'active' => true
    ],
    [
        'id' => 'camisa-de-futebol',
        'name' => 'Camisa de Futebol',
        'description' => 'Camisas oficiais dos seus times!',
        'min_bet' => 2.00,
        'max_bet' => 30.00,
        'win_chance' => 0.35,
        'multipliers' => [1.5, 2, 4, 6],
        'image' => '/assets/scratch.png',
        'active' => true
    ]
];

http_response_code(200);
echo json_encode($game_types);
?>