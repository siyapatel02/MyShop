<?php

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

$id = (int)($_GET['id'] ?? 0);
if($id <= 0){ jsonResponse(false, 'Invalid product id', [], 422); }

try{
    $database = new Database();
    $db = $database->connect();

    $query = "
        SELECT products.id, products.name, products.description, products.price, products.image, products.category_id, products.created_at,
               categories.name AS category_name,
               COALESCE(AVG(reviews.rating), 0) AS average_rating,
               COUNT(reviews.id) AS total_reviews
        FROM products
        LEFT JOIN categories ON products.category_id = categories.id
        LEFT JOIN reviews ON reviews.product_id = products.id AND reviews.status = 'active'
        WHERE products.id = :id
        GROUP BY products.id, products.name, products.description, products.price, products.image, products.category_id, products.created_at, categories.name
        LIMIT 1
    ";

    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$product){ jsonResponse(false, 'Product not found', [], 404); }

    $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))) . '/uploads/products/';

    $product['id'] = (int)$product['id'];
    $product['price'] = (float)$product['price'];
    $product['category_id'] = $product['category_id'] !== null ? (int)$product['category_id'] : null;
    $product['average_rating'] = round((float)$product['average_rating'], 1);
    $product['total_reviews'] = (int)$product['total_reviews'];
    $product['image_url'] = $baseUrl . $product['image'];
    $product['image'] = $product['image_url'];
    $product['images'] = [$product['image_url']];

    try{
        $tableCheck = $db->query("SHOW TABLES LIKE 'product_images'");
        if($tableCheck && $tableCheck->rowCount() > 0){
            $imgStmt = $db->prepare("SELECT image FROM product_images WHERE product_id = :id ORDER BY id ASC");
            $imgStmt->execute([':id' => $id]);
            $extraImages = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
            foreach($extraImages as $img){
                $url = $baseUrl . $img;
                if(!in_array($url, $product['images'])){ $product['images'][] = $url; }
            }
        }
    }catch(Exception $e){ }

    jsonResponse(true, 'Product details fetched', $product);
}catch(Exception $e){
    jsonResponse(false, 'Failed to fetch product details', [], 500);
}
