<?php

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/jwt.php';

function getBearerToken(){

    $headers = getallheaders();

    $authHeader =
        $headers['Authorization']
        ?? $headers['authorization']
        ?? '';

    if(!$authHeader){
        return null;
    }

    if(!preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)){
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
    'current_password',
    'new_password',
    'confirm_password'
];

foreach($data as $key => $value){
    if(!in_array($key, $allowedFields)){
        jsonResponse(false, 'Invalid field: ' . $key, [], 422);
    }
}

$currentPassword = trim($data['current_password'] ?? '');
$newPassword = trim($data['new_password'] ?? '');
$confirmPassword = trim($data['confirm_password'] ?? '');

if(
    $currentPassword === '' ||
    $newPassword === '' ||
    $confirmPassword === ''
){
    jsonResponse(false, 'All fields are required', [], 422);
}

if(strlen($newPassword) < 6){
    jsonResponse(false, 'New password must be at least 6 characters', [], 422);
}

if($newPassword !== $confirmPassword){
    jsonResponse(false, 'New password and confirm password do not match', [], 422);
}

try{

    $database = new Database();
    $db = $database->connect();

    $query = "
        SELECT password
        FROM users
        WHERE id = :id
        LIMIT 1
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':id' => $user['id']
    ]);

    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$profile){
        jsonResponse(false, 'User not found', [], 404);
    }

    if(!password_verify($currentPassword, $profile['password'])){
        jsonResponse(false, 'Current password is incorrect', [], 422);
    }

    $hashedPassword = password_hash(
        $newPassword,
        PASSWORD_BCRYPT
    );

    $updateQuery = "
        UPDATE users
        SET password = :password
        WHERE id = :id
    ";

    $updateStmt = $db->prepare($updateQuery);

    $updateStmt->execute([
        ':password' => $hashedPassword,
        ':id' => $user['id']
    ]);

    jsonResponse(
        true,
        'Password changed successfully'
    );

}catch(Exception $e){

    jsonResponse(
        false,
        'Failed to change password'
    );
}