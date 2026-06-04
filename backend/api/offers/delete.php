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
$admin = verifyToken($token);

if (!$admin) {
    jsonResponse(false, 'Invalid token', null, 401);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$id = intval($data['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(false, 'Offer id is required');
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $query = "DELETE FROM offers WHERE id = :id";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':id' => $id
    ]);

    if ($stmt->rowCount() <= 0) {
        jsonResponse(false, 'Offer not found');
        exit;
    }

    jsonResponse(true, 'Offer deleted successfully');

} catch (Exception $e) {
    jsonResponse(false, 'Offer delete failed: ' . $e->getMessage(), null, 500);
}