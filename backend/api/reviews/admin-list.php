<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../middleware/adminMiddleware.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

try {
    $database = new Database();
    $db = $database->connect();

    $query = "
        SELECT
            reviews.id,
            reviews.rating,
            reviews.review,
            reviews.status,
            reviews.created_at,
            users.name AS user_name,
            products.name AS product_name
        FROM reviews
        INNER JOIN users ON reviews.user_id = users.id
        INNER JOIN products ON reviews.product_id = products.id
        ORDER BY reviews.id DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();

    jsonResponse(
        true,
        'Reviews fetched successfully',
        $stmt->fetchAll(PDO::FETCH_ASSOC)
    );

} catch (Exception $e) {
    jsonResponse(false, 'Failed to fetch reviews: ' . $e->getMessage(), null, 500);
}