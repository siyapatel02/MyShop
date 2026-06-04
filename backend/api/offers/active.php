<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

try {
    $database = new Database();
    $db = $database->connect();

    $today = date('Y-m-d');

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
            start_date,
            expiry_date
        FROM offers
        WHERE status = 'active'
        AND (start_date IS NULL OR start_date <= :today)
        AND (expiry_date IS NULL OR expiry_date >= :today)
        ORDER BY id DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':today' => $today
    ]);

    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(true, 'Active offers fetched successfully', $offers);

} catch (Exception $e) {
    jsonResponse(false, 'Failed to fetch active offers: ' . $e->getMessage(), null, 500);
}