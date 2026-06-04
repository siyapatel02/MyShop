<?php

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/jwt.php';

function getBearerToken(){

    $headers = getallheaders();

    if(!isset($headers['Authorization'])){
        return null;
    }

    if(!preg_match('/Bearer\s+(\S+)/', $headers['Authorization'], $matches)){
        return null;
    }

    return $matches[1];
}

$token = getBearerToken();

if(!$token){
    jsonResponse(false, 'Unauthorized', [], 401);
}

$user = verifyToken($token);

if(!$user || empty($user['id'])){
    jsonResponse(false, 'Invalid token', [], 401);
}

try{

    $database = new Database();
    $db = $database->connect();

    $query = "
        SELECT
            wishlist.id AS wishlist_id,
            products.id AS product_id,
            products.name,
            products.price,
            products.image
        FROM wishlist
        INNER JOIN products
        ON wishlist.product_id = products.id
        WHERE wishlist.user_id = :user_id
        ORDER BY wishlist.id DESC
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':user_id' => $user['id']
    ]);

    $items =
    $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($items as &$item){

        $item['wishlist_id'] =
        (int)$item['wishlist_id'];

        $item['product_id'] =
        (int)$item['product_id'];

        $item['price'] =
        (float)$item['price'];

        $item['image_url'] =
        'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))) . '/uploads/products/' .
        $item['image'];
    }

    jsonResponse(
        true,
        'Wishlist fetched successfully',
        $items
    );

}catch(Exception $e){

    jsonResponse(
        false,
        'Failed to fetch wishlist',
        [],
        500
    );
}