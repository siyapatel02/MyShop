<?php

require_once __DIR__ . '/../../config/cors.php';

require_once __DIR__ . '/../../config/database.php';

require_once __DIR__ . '/../../helpers/response.php';

require_once __DIR__ . '/../../helpers/jwt.php';

require_once __DIR__ . '/../../services/ProductService.php';

require_once __DIR__ . '/../../models/Cart.php';

$headers = getallheaders();

if(!isset($headers['Authorization'])){

    jsonResponse(
        false,
        'Unauthorized'
    );
}

$token = str_replace(

    'Bearer ',

    '',

    $headers['Authorization']
);

$user =
verifyToken($token);

$database = new Database();

$db = $database->connect();

$cartModel = new Cart($db);

$items =
$cartModel->getItems(
    $user['id']
);

$formatted = [];

foreach($items as $item){

    $formatted[] =
    ProductService::format($item);
}

jsonResponse(
    true,
    'Cart fetched',
    $formatted
);