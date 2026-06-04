<?php

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/jwt.php';
require_once __DIR__ . '/../../models/Order.php';

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

if($_SERVER['REQUEST_METHOD'] !== 'GET'){
    jsonResponse(false, 'Only GET method allowed', [], 405);
}

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

$token = getBearerToken();

if(!$token){
    jsonResponse(false, 'Authorization token missing', [], 401);
}

$user = verifyToken($token);

if(!$user || empty($user['id'])){
    jsonResponse(false, 'Invalid token', [], 401);
}

/*
|--------------------------------------------------------------------------
| FETCH SAVED ADDRESSES
|--------------------------------------------------------------------------
*/

try {

    $database = new Database();
    $db = $database->connect();

    $orderModel = new Order($db);

    $addresses = $orderModel->getSavedAddresses($user['id']);

    jsonResponse(true, 'Addresses fetched successfully', $addresses);

} catch(Exception $e){

    jsonResponse(false, 'Server error while fetching addresses', [], 500);
}
