<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/jwt.php';

try {

    $headers = getallheaders();

    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (!$authHeader) {
        jsonResponse(false, 'Authorization header missing', null, 401);
        exit;
    }

    $token = str_replace('Bearer ', '', $authHeader);

    $user = verifyToken($token);

    if (!$user) {
        jsonResponse(false, 'Invalid or expired token', null, 401);
        exit;
    }

    $userId = $user['id'];

    $database = new Database();
    $db = $database->connect();

    $query = "
        SELECT 
            id,
            user_id,
            fullname,
            phone,
            pincode,
            address_line,
            city,
            state,
            country,
            address_type,
            label,
            is_default,
            created_at
        FROM user_addresses
        WHERE user_id = ?
        ORDER BY last_used_at DESC, is_default DESC, id DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);

    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(true, 'Addresses fetched successfully', $addresses);

} catch (Throwable $e) {

    jsonResponse(false, $e->getMessage(), null, 500);
}