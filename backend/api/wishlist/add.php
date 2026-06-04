<?php

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/jwt.php';
require_once __DIR__ . '/../../models/Wishlist.php';

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

$allowedFields = ['product_id'];

foreach($data as $key => $value){

    if(!in_array($key, $allowedFields)){
        jsonResponse(false, 'Invalid field: ' . $key, [], 422);
    }
}

$productId = (int)($data['product_id'] ?? 0);

if($productId <= 0){
    jsonResponse(false, 'Product ID required', [], 422);
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

    $wishlistModel = new Wishlist($db);

    $added =
    $wishlistModel->add(
        $user['id'],
        $productId
    );

    if(!$added){
        jsonResponse(false, 'Already in wishlist', [], 409);
    }

    jsonResponse(
        true,
        'Added to wishlist'
    );

}catch(Exception $e){

    jsonResponse(
        false,
        'Failed to add wishlist',
        [],
        500
    );
}