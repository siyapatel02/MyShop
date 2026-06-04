<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/jwt.php';

$productId = intval($_GET['product_id'] ?? 0);

if ($productId <= 0) {
    jsonResponse(false, 'Product id is required');
    exit;
}

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$authHeader) {
    jsonResponse(true, 'Login required', [
        'can_review' => false,
        'reason' => 'login_required'
    ]);
    exit;
}

$token = str_replace('Bearer ', '', $authHeader);
$user = verifyToken($token);

if (!$user || empty($user['id'])) {
    jsonResponse(true, 'Login required', [
        'can_review' => false,
        'reason' => 'login_required'
    ]);
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $query = "
        SELECT orders.id
        FROM orders
        INNER JOIN order_items
        ON orders.id = order_items.order_id
        WHERE orders.user_id = :user_id
        AND order_items.product_id = :product_id
        AND orders.status = 'delivered'
        LIMIT 1
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':user_id' => $user['id'],
        ':product_id' => $productId
    ]);

    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        jsonResponse(true, 'Not eligible to review', [
            'can_review' => false,
            'reason' => 'not_delivered_buyer'
        ]);
        exit;
    }

    jsonResponse(true, 'User can review this product', [
        'can_review' => true,
        'reason' => 'delivered_buyer'
    ]);

} catch (Exception $e) {
    jsonResponse(false, 'Failed to check review eligibility: ' . $e->getMessage(), null, 500);
}