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

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    jsonResponse(false, 'Only POST method allowed', [], 405);
}

$data = json_decode(file_get_contents("php://input"), true);

if(!is_array($data)){
    jsonResponse(false, 'Invalid JSON body', [], 400);
}

$allowedFields = ['id'];

foreach($data as $key => $value){
    if(!in_array($key, $allowedFields)){
        jsonResponse(false, 'Invalid field: ' . $key, [], 422);
    }
}

$id = (int)($data['id'] ?? 0);

if($id <= 0){
    jsonResponse(false, 'Product ID required', [], 422);
}

try{

    $database = new Database();
    $db = $database->connect();

    $findQuery = "
        SELECT image
        FROM products
        WHERE id = :id
        LIMIT 1
    ";

    $findStmt = $db->prepare($findQuery);
    $findStmt->execute([
        ':id' => $id
    ]);

    $product = $findStmt->fetch(PDO::FETCH_ASSOC);

    if(!$product){
        jsonResponse(false, 'Product not found', [], 404);
    }

    $deleteQuery = "
        DELETE FROM products
        WHERE id = :id
    ";

    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->execute([
        ':id' => $id
    ]);

    $uploadDir = __DIR__ . '/../../uploads/products/';

    if(
        !empty($product['image']) &&
        file_exists($uploadDir . $product['image'])
    ){
        unlink($uploadDir . $product['image']);
    }

    jsonResponse(
        true,
        'Product deleted successfully'
    );

}catch(Exception $e){

    jsonResponse(
        false,
        'Product delete failed',
        [],
        500
    );
}