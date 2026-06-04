<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/jwt.php';

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$authHeader) {
    jsonResponse(false, 'Token missing', null, 401);
    exit;
}

$token = str_replace('Bearer ', '', $authHeader);
$user = verifyToken($token);

if (!$user || empty($user['id'])) {
    jsonResponse(false, 'Invalid token', null, 401);
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $query = "
        SELECT 
            id,
            name,
            email,
            phone,
            dob,
            gender,
            created_at
        FROM users
        WHERE id = :id
        LIMIT 1
    ";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':id' => $user['id']
    ]);

    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        jsonResponse(false, 'User not found', null, 404);
        exit;
    }

    jsonResponse(true, 'Profile fetched successfully', $profile);

} catch (Exception $e) {
    jsonResponse(false, 'Failed to fetch profile', null, 500);
}