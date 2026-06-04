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
    'cart_id'
];

foreach($data as $key => $value){

    if(!in_array($key, $allowedFields)){
        jsonResponse(false, 'Invalid field: ' . $key, [], 422);
    }
}

$cartId = (int)($data['cart_id'] ?? 0);

if($cartId <= 0){
    jsonResponse(false, 'Cart ID required', [], 422);
}

try{

    $database = new Database();
    $db = $database->connect();

    $query = "
        DELETE FROM cart
        WHERE id = :id
        AND user_id = :user_id
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':id' => $cartId,
        ':user_id' => $user['id']
    ]);

    if($stmt->rowCount() <= 0){
        jsonResponse(false, 'Cart item not found', [], 404);
    }

    jsonResponse(
        true,
        'Cart item removed'
    );

}catch(Exception $e){

    jsonResponse(
        false,
        'Failed to remove cart item',
        [],
        500
    );
}   