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

if (!$user) {
    jsonResponse(false, 'Invalid token', null, 401);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    jsonResponse(false, 'Invalid JSON data');
    exit;
}

$id = intval($data['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(false, 'Address id is required');
    exit;
}

try {

    $database = new Database();
    $db = $database->connect();

    $query = "
        DELETE FROM user_addresses
        WHERE id = :id
        AND user_id = :user_id
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':id' => $id,
        ':user_id' => $user['id']
    ]);

    if ($stmt->rowCount() <= 0) {
        jsonResponse(false, 'Address not found');
        exit;
    }

    jsonResponse(true, 'Address deleted successfully');

} catch (Exception $e) {
    jsonResponse(false, 'Address delete failed: ' . $e->getMessage(), null, 500);
}