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

$admin = verifyToken($token);

if(!$admin || empty($admin['id']) || ($admin['role'] ?? '') !== 'admin'){
    jsonResponse(false, 'Admin access required', [], 403);
}

$orderId = (int)($_GET['id'] ?? 0);

if($orderId <= 0){
    jsonResponse(false, 'Order ID required', [], 422);
}

try{

    $database = new Database();
    $db = $database->connect();

    $orderQuery = "
        SELECT
            orders.id,
            orders.user_id,
            users.name AS customer_name,
            users.email AS customer_email,
            orders.total,
            orders.address,
            orders.status,
            orders.payment_method,
            orders.created_at,
            orders.updated_at
        FROM orders
        LEFT JOIN users
        ON orders.user_id = users.id
        WHERE orders.id = :id
        LIMIT 1
    ";

    $orderStmt = $db->prepare($orderQuery);

    $orderStmt->execute([
        ':id' => $orderId
    ]);

    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

    if(!$order){
        jsonResponse(false, 'Order not found', [], 404);
    }

    $itemsQuery = "
        SELECT
            order_items.id,
            order_items.product_id,
            order_items.quantity,
            order_items.price,
            products.name,
            products.image
        FROM order_items
        LEFT JOIN products
        ON order_items.product_id = products.id
        WHERE order_items.order_id = :order_id
    ";

    $itemsStmt = $db->prepare($itemsQuery);

    $itemsStmt->execute([
        ':order_id' => $orderId
    ]);

    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($items as &$item){

        $item['image_url'] =
        'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))) . '/uploads/products/' .
        $item['image'];

        $item['line_total'] =
        (float)$item['price'] * (int)$item['quantity'];
    }

    jsonResponse(
        true,
        'Order details fetched successfully',
        [
            'order' => $order,
            'items' => $items
        ]
    );

}catch(Exception $e){

    jsonResponse(false, 'Failed to fetch order details', [], 500);
}