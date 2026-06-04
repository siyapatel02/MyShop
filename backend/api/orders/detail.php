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

    jsonResponse(
        false,
        'Unauthorized',
        [],
        401
    );
}

$user = verifyToken($token);

if(!$user || empty($user['id'])){

    jsonResponse(
        false,
        'Invalid token',
        [],
        401
    );
}

$orderId =
(int)($_GET['id'] ?? 0);

if($orderId <= 0){

    jsonResponse(
        false,
        'Invalid order id'
    );
}

try{

    $database = new Database();

    $db = $database->connect();

    $orderQuery = "
        SELECT
            *
        FROM orders
        WHERE id = :id
        AND user_id = :user_id
    ";

    $orderStmt =
    $db->prepare($orderQuery);

    $orderStmt->execute([

        ':id' => $orderId,

        ':user_id' => $user['id']
    ]);

    $order =
    $orderStmt->fetch(PDO::FETCH_ASSOC);

    if(!$order){

        jsonResponse(
            false,
            'Order not found'
        );
    }

    $itemsQuery = "
        SELECT
            order_items.*,
            products.name,
            products.image
        FROM order_items

        INNER JOIN products
        ON order_items.product_id = products.id

        WHERE order_items.order_id = :order_id
    ";

    $itemsStmt =
    $db->prepare($itemsQuery);

    $itemsStmt->execute([

        ':order_id' => $orderId
    ]);

    $items =
    $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(

        true,

        'Order details fetched',

        [

            'order' => $order,

            'items' => $items
        ]
    );

}catch(Exception $e){

    jsonResponse(
        false,
        'Failed to fetch order details'
    );
}