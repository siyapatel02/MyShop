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

$userId = (int)($_GET['id'] ?? 0);

if($userId <= 0){
    jsonResponse(false, 'User ID required', [], 422);
}

try{

    $database = new Database();
    $db = $database->connect();

    $userQuery = "
        SELECT
            id,
            name,
            email,
            phone,
            dob,
            gender,
            status,
            created_at
        FROM users
        WHERE id = :id
        LIMIT 1
    ";

    $userStmt = $db->prepare($userQuery);

    $userStmt->execute([
        ':id' => $userId
    ]);

    $user =
    $userStmt->fetch(PDO::FETCH_ASSOC);

    if(!$user){
        jsonResponse(false, 'User not found', [], 404);
    }

    $ordersQuery = "
        SELECT
            id,
            total,
            status,
            payment_method,
            created_at
        FROM orders
        WHERE user_id = :user_id
        ORDER BY id DESC
    ";

    $ordersStmt = $db->prepare($ordersQuery);

    $ordersStmt->execute([
        ':user_id' => $userId
    ]);

    $orders =
    $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

    $addressQuery = "
        SELECT
            *
        FROM user_addresses
        WHERE user_id = :user_id
        ORDER BY is_default DESC, id DESC
    ";

    $addressStmt = $db->prepare($addressQuery);

    $addressStmt->execute([
        ':user_id' => $userId
    ]);

    $addresses =
    $addressStmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(
        true,
        'User details fetched successfully',
        [
            'user' => $user,
            'orders' => $orders,
            'addresses' => $addresses
        ]
    );

}catch(Exception $e){

    jsonResponse(
        false,
        'Failed to fetch user details',
        [],
        500
    );
}