<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

try {
    $database = new Database();
    $db = $database->connect();

    $query = "
        SELECT
            id,
            code,
            title,
            description,
            discount_type,
            discount_value,
            minimum_order,
            max_discount,
            first_order_only,
            start_date,
            expiry_date,
            status,
            created_at
        FROM offers
        ORDER BY id DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(true, 'Offers fetched successfully', $offers);

} catch (Exception $e) {
    jsonResponse(false, 'Failed to fetch offers: ' . $e->getMessage(), null, 500);
}