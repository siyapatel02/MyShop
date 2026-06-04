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
    jsonResponse(false, 'Banner id is required');
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $selectQuery = "
        SELECT image
        FROM banners
        WHERE id = :id
        LIMIT 1
    ";

    $selectStmt = $db->prepare($selectQuery);
    $selectStmt->execute([
        ':id' => $id
    ]);

    $banner = $selectStmt->fetch(PDO::FETCH_ASSOC);

    if (!$banner) {
        jsonResponse(false, 'Banner not found');
        exit;
    }

    $deleteQuery = "
        DELETE FROM banners
        WHERE id = :id
    ";

    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->execute([
        ':id' => $id
    ]);

    $imagePath = __DIR__ . '/../../uploads/banners/' . $banner['image'];

    if (file_exists($imagePath)) {
        unlink($imagePath);
    }

    jsonResponse(true, 'Banner deleted successfully');

} catch (Exception $e) {
    jsonResponse(false, 'Banner delete failed: ' . $e->getMessage(), null, 500);
}