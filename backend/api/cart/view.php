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
            cart.id,
            cart.quantity,
            products.id AS product_id,
            products.name,
            products.price,
            products.image
        FROM cart
        INNER JOIN products
        ON cart.product_id = products.id
        WHERE cart.user_id = :user_id
        ORDER BY cart.id DESC
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':user_id' => $user['id']
    ]);

    $items =
    $stmt->fetchAll(PDO::FETCH_ASSOC);

    $subtotal = 0;

    foreach($items as &$item){

        $item['id'] =
        (int)$item['id'];

        $item['product_id'] =
        (int)$item['product_id'];

        $item['quantity'] =
        (int)$item['quantity'];

        $item['price'] =
        (float)$item['price'];

        $item['total'] =
        $item['price'] * $item['quantity'];

        $item['image_url'] =
        'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))) . '/uploads/products/' .
        $item['image'];

        $subtotal +=
        $item['total'];
    }

    jsonResponse(
        true,
        'Cart fetched successfully',
        [
            'items' => $items,
            'subtotal' => $subtotal,
            'count' => count($items)
        ]
    );

}catch(Exception $e){

    jsonResponse(
        false,
        'Failed to fetch cart',
        [],
        500
    );
}