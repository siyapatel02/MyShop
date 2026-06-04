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

$data = json_decode(file_get_contents("php://input"), true);

if(!is_array($data)){
    jsonResponse(false, 'Invalid JSON', [], 400);
}

$userId = (int)($data['user_id'] ?? 0);
$status = trim($data['status'] ?? '');

if($userId <= 0){
    jsonResponse(false, 'User ID required', [], 422);
}

if(!in_array($status, ['active', 'blocked'])){
    jsonResponse(false, 'Invalid status', [], 422);
}

try{

    $database = new Database();
    $db = $database->connect();

    $query = "
        UPDATE users
        SET status = :status
        WHERE id = :id
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':status' => $status,
        ':id' => $userId
    ]);

    jsonResponse(
        true,
        'User status updated successfully'
    );

}catch(Exception $e){

    jsonResponse(
        false,
        'Failed to update user status',
        [],
        500
    );
}