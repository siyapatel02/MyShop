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

$data = json_decode(file_get_contents("php://input"), true);

$productId = intval($data['product_id'] ?? 0);
$rating = intval($data['rating'] ?? 0);
$review = trim($data['review'] ?? '');

if ($productId <= 0) {
    jsonResponse(false, 'Product id is required');
    exit;
}

if ($rating < 1 || $rating > 5) {
    jsonResponse(false, 'Rating must be between 1 and 5');
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    /*
    |--------------------------------------------------------------------------
    | VERIFIED BUYER CHECK
    |--------------------------------------------------------------------------
    */

    $orderCheckQuery = "
        SELECT
            orders.id
        FROM orders
        INNER JOIN order_items
        ON orders.id = order_items.order_id
        WHERE orders.user_id = :user_id
        AND order_items.product_id = :product_id
        AND orders.status = 'delivered'
        LIMIT 1
    ";

    $orderCheckStmt = $db->prepare($orderCheckQuery);

    $orderCheckStmt->execute([
        ':user_id' => $user['id'],
        ':product_id' => $productId
    ]);

    $verifiedOrder = $orderCheckStmt->fetch(PDO::FETCH_ASSOC);

    if (!$verifiedOrder) {
        jsonResponse(
            false,
            'You can review this product only after your order is delivered.',
            null,
            403
        );
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE REVIEW AS PENDING
    |--------------------------------------------------------------------------
    */

    $query = "
        INSERT INTO reviews
        (
            user_id,
            product_id,
            rating,
            review,
            status
        )
        VALUES
        (
            :user_id,
            :product_id,
            :rating,
            :review,
            'pending'
        )
        ON DUPLICATE KEY UPDATE
            rating = VALUES(rating),
            review = VALUES(review),
            status = 'pending',
            created_at = CURRENT_TIMESTAMP
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':user_id' => $user['id'],
        ':product_id' => $productId,
        ':rating' => $rating,
        ':review' => $review
    ]);

    jsonResponse(
        true,
        'Review submitted successfully. It will be visible after admin approval.'
    );

} catch (Exception $e) {
    jsonResponse(false, 'Review save failed: ' . $e->getMessage(), null, 500);
}