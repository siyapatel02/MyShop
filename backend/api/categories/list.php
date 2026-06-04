<?php

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

try{

    $database = new Database();
    $db = $database->connect();

    $query = "
        SELECT
            id,
            name,
            slug,
            parent_id,
            status
        FROM categories
        WHERE status = 'active'
        ORDER BY parent_id ASC, name ASC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(
        true,
        'Categories fetched successfully',
        $categories
    );

}catch(Exception $e){

    jsonResponse(
        false,
        'Failed to fetch categories',
        [],
        500
    );
}