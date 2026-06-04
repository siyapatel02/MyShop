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

try{

    $database = new Database();

    $db = $database->connect();

    $query = "
        SELECT
            id,
            total,
            address,
            status,
            payment_method,
            coupon_code,
            discount_amount,
            created_at,
            updated_at
        FROM orders
        WHERE user_id = :user_id
        ORDER BY id DESC
    ";

    $stmt = $db->prepare($query);

    $stmt->bindParam(
        ':user_id',
        $user['id']
    );

    $stmt->execute();

    $orders =
    $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(
        true,
        'Orders fetched successfully',
        $orders
    );

}catch(Exception $e){

    jsonResponse(
        false,
        'Failed to fetch orders'
    );
}