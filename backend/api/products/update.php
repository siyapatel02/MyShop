<?php

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/jwt.php';

function getBearerToken(){

    $headers = getallheaders();

    if(!isset($headers['Authorization'])){
        return null;
    }

    if(!preg_match('/Bearer\s+(\S+)/', $headers['Authorization'], $matches)){
        return null;
    }

    return $matches[1];
}

$token = getBearerToken();

if(!$token){
    jsonResponse(false, 'Unauthorized', [], 401);
}

$admin = verifyToken($token);

if(!$admin || empty($admin['id']) || ($admin['role'] ?? '') !== 'admin'){
    jsonResponse(false, 'Admin access required', [], 403);
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    jsonResponse(false, 'Only POST method allowed', [], 405);
}

$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = trim($_POST['price'] ?? '');
$categoryId = trim($_POST['category_id'] ?? '');

if($id <= 0){
    jsonResponse(false, 'Product ID required', [], 422);
}

if($name === '' || $description === '' || $price === ''){
    jsonResponse(false, 'Name, description and price are required', [], 422);
}

if(!is_numeric($price) || $price <= 0){
    jsonResponse(false, 'Price must be greater than 0', [], 422);
}

if($categoryId !== '' && (!is_numeric($categoryId) || $categoryId <= 0)){
    jsonResponse(false, 'Invalid category id', [], 422);
}

try{

    $database = new Database();
    $db = $database->connect();

    $findQuery = "
        SELECT image
        FROM products
        WHERE id = :id
        LIMIT 1
    ";

    $findStmt = $db->prepare($findQuery);
    $findStmt->execute([
        ':id' => $id
    ]);

    $product = $findStmt->fetch(PDO::FETCH_ASSOC);

    if(!$product){
        jsonResponse(false, 'Product not found', [], 404);
    }

    $imageName = $product['image'];

    if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK){

        $allowedTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp'
        ];

        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if(!in_array($fileType, $allowedTypes)){
            jsonResponse(false, 'Only JPG, PNG and WEBP images are allowed', [], 422);
        }

        $maxSize = 2 * 1024 * 1024;

        if($_FILES['image']['size'] > $maxSize){
            jsonResponse(false, 'Image size must be less than 2MB', [], 422);
        }

        $uploadDir = __DIR__ . '/../../uploads/products/';

        if(!is_dir($uploadDir)){
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        $newImageName =
            time() .
            '_' .
            uniqid() .
            '.' .
            strtolower($extension);

        $uploadPath = $uploadDir . $newImageName;

        if(!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)){
            jsonResponse(false, 'Image upload failed', [], 500);
        }

        if(
            !empty($product['image']) &&
            file_exists($uploadDir . $product['image'])
        ){
            unlink($uploadDir . $product['image']);
        }

        $imageName = $newImageName;
    }

    $updateQuery = "
        UPDATE products
        SET
            name = :name,
            description = :description,
            price = :price,
            image = :image,
            category_id = :category_id
        WHERE id = :id
    ";

    $updateStmt = $db->prepare($updateQuery);

    $updateStmt->execute([
        ':name' => $name,
        ':description' => $description,
        ':price' => $price,
        ':image' => $imageName,
        ':category_id' => $categoryId !== '' ? $categoryId : null,
        ':id' => $id
    ]);

    jsonResponse(
        true,
        'Product updated successfully',
        [
            'product_id' => $id,
            'image' => $imageName
        ]
    );

}catch(Exception $e){

    jsonResponse(false, 'Product update failed', [], 500);
}