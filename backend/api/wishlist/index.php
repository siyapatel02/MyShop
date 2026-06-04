<?php

require_once __DIR__ . '/../../config/cors.php';

require_once __DIR__ . '/../../config/database.php';

require_once __DIR__ . '/../../helpers/response.php';

require_once __DIR__ . '/../../helpers/jwt.php';

require_once __DIR__ . '/../../models/Wishlist.php';

require_once __DIR__ . '/../../services/ProductService.php';

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

$wishlistModel =
new Wishlist($db);

$items =
$wishlistModel->getItems(
    $user['id']
);

$formatted = [];

foreach($items as $item){

    $formatted[] =
    ProductService::format($item);
}

jsonResponse(
    true,
    'Wishlist fetched',
    $formatted
);