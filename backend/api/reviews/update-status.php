<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../middleware/adminMiddleware.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

$data = json_decode(file_get_contents("php://input"), true);

$reviewId = intval($data['review_id'] ?? 0);
$status = trim($data['status'] ?? '');

$allowedStatus = ['pending', 'active', 'hidden'];

if ($reviewId <= 0) {
    jsonResponse(false, 'Review id is required');
    exit;
}

if (!in_array($status, $allowedStatus)) {
    jsonResponse(false, 'Invalid status');
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $query = "
        UPDATE reviews
        SET status = :status
        WHERE id = :id
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':status' => $status,
        ':id' => $reviewId
    ]);

    jsonResponse(true, 'Review status updated successfully');

} catch (Exception $e) {
    jsonResponse(false, 'Failed to update review: ' . $e->getMessage(), null, 500);
}