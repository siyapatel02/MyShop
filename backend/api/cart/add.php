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

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    jsonResponse(false, 'Only POST method allowed', [], 405);
}

$data = json_decode(file_get_contents("php://input"), true);

if(!is_array($data)){
    jsonResponse(false, 'Invalid JSON body', [], 400);
}

$allowedFields = [
    'product_id',
    'quantity'
];

foreach($data as $key => $value){

    if(!in_array($key, $allowedFields)){
        jsonResponse(false, 'Invalid field: ' . $key, [], 422);
    }
}

$productId = (int)($data['product_id'] ?? 0);
$quantity = (int)($data['quantity'] ?? 1);

if($productId <= 0){
    jsonResponse(false, 'Product ID required', [], 422);
}

if($quantity < 1){
    jsonResponse(false, 'Quantity must be greater than 0', [], 422);
}

try{

    $database = new Database();
    $db = $database->connect();

    $productQuery = "
        SELECT id
        FROM products
        WHERE id = :id
        LIMIT 1
    ";

    $productStmt = $db->prepare($productQuery);

    $productStmt->execute([
        ':id' => $productId
    ]);

    if(!$productStmt->fetch(PDO::FETCH_ASSOC)){
        jsonResponse(false, 'Product not found', [], 404);
    }

    $checkQuery = "
        SELECT id, quantity
        FROM cart
        WHERE user_id = :user_id
        AND product_id = :product_id
        LIMIT 1
    ";

    $checkStmt = $db->prepare($checkQuery);

    $checkStmt->execute([
        ':user_id' => $user['id'],
        ':product_id' => $productId
    ]);

    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if($existing){

        $newQuantity =
        (int)$existing['quantity'] + $quantity;

        $updateQuery = "
            UPDATE cart
            SET quantity = :quantity
            WHERE id = :id
            AND user_id = :user_id
        ";

        $updateStmt = $db->prepare($updateQuery);

        $updateStmt->execute([
            ':quantity' => $newQuantity,
            ':id' => $existing['id'],
            ':user_id' => $user['id']
        ]);

        jsonResponse(
            true,
            'Cart updated successfully',
            [
                'cart_id' => (int)$existing['id'],
                'quantity' => $newQuantity
            ]
        );
    }

    $query = "
        INSERT INTO cart
        (
            user_id,
            product_id,
            quantity
        )
        VALUES
        (
            :user_id,
            :product_id,
            :quantity
        )
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':user_id' => $user['id'],
        ':product_id' => $productId,
        ':quantity' => $quantity
    ]);

    jsonResponse(
        true,
        'Product added to cart',
        [
            'cart_id' => (int)$db->lastInsertId(),
            'quantity' => $quantity
        ]
    );

}catch(Exception $e){

    jsonResponse(
        false,
        'Failed to add cart',
        [],
        500
    );
}