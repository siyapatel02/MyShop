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

$allowedFields = ['order_id', 'status'];

foreach($data as $key => $value){
    if(!in_array($key, $allowedFields)){
        jsonResponse(false, 'Invalid field: ' . $key, [], 422);
    }
}

$orderId = (int)($data['order_id'] ?? 0);
$status = trim($data['status'] ?? '');

$allowedStatus = [
    'pending',
    'shipped',
    'delivered'
];

if($orderId <= 0){
    jsonResponse(false, 'Order ID required', [], 422);
}

if(!in_array($status, $allowedStatus)){
    jsonResponse(false, 'Invalid order status', [], 422);
}

try{

    $database = new Database();
    $db = $database->connect();

    $query = "
        UPDATE orders
        SET status = :status
        WHERE id = :id
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':status' => $status,
        ':id' => $orderId
    ]);

    if($stmt->rowCount() <= 0){
        jsonResponse(false, 'Order not found or status unchanged', [], 404);
    }

    jsonResponse(
        true,
        'Order status updated successfully'
    );

}catch(Exception $e){

    jsonResponse(false, 'Failed to update order status', [], 500);
}