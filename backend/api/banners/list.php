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
            title,
            subtitle,
            image,
            CONCAT('../../backend/uploads/banners/', image) AS image_url,
            button_text,
            button_link,
            status,
            created_at
        FROM banners
        ORDER BY id DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(true, 'Banners fetched successfully', $banners);

} catch (Exception $e) {
    jsonResponse(false, 'Failed to fetch banners: ' . $e->getMessage(), null, 500);
}