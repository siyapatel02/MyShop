<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

$productId = intval($_GET['product_id'] ?? 0);

if ($productId <= 0) {
    jsonResponse(false, 'Product id is required');
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $summaryQuery = "
        SELECT
            COUNT(*) AS total_reviews,
            COALESCE(AVG(rating), 0) AS average_rating
        FROM reviews
        WHERE product_id = :product_id
        AND status = 'active'
    ";

    $summaryStmt = $db->prepare($summaryQuery);
    $summaryStmt->execute([
        ':product_id' => $productId
    ]);

    $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

    $reviewsQuery = "
        SELECT
            reviews.id,
            reviews.rating,
            reviews.review,
            reviews.created_at,
            users.name AS user_name
        FROM reviews
        INNER JOIN users
        ON reviews.user_id = users.id
        WHERE reviews.product_id = :product_id
        AND reviews.status = 'active'
        ORDER BY reviews.id DESC
    ";

    $reviewsStmt = $db->prepare($reviewsQuery);
    $reviewsStmt->execute([
        ':product_id' => $productId
    ]);

    $reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(true, 'Reviews fetched successfully', [
        'summary' => [
            'total_reviews' => intval($summary['total_reviews']),
            'average_rating' => round(floatval($summary['average_rating']), 1)
        ],
        'reviews' => $reviews
    ]);

} catch (Exception $e) {
    jsonResponse(false, 'Failed to fetch reviews: ' . $e->getMessage(), null, 500);
}