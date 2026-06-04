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

$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = trim($_POST['price'] ?? '');
$categoryId = trim($_POST['category_id'] ?? '');

if($name === '' || $description === '' || $price === ''){
    jsonResponse(false, 'Name, description and price are required', [], 422);
}

if(!is_numeric($price) || $price <= 0){
    jsonResponse(false, 'Price must be greater than 0', [], 422);
}

if($categoryId !== '' && (!is_numeric($categoryId) || $categoryId <= 0)){
    jsonResponse(false, 'Invalid category id', [], 422);
}

$imageFiles = $_FILES['image'] ?? null;
if(!$imageFiles && isset($_FILES['image']['name'][0])){
    $imageFiles = $_FILES['image'];
}
if(!$imageFiles){
    jsonResponse(false, 'Product image is required', [], 422);
}

if(is_array($imageFiles['name'])){
    $firstFile = [
        'name' => $imageFiles['name'][0] ?? '',
        'type' => $imageFiles['type'][0] ?? '',
        'tmp_name' => $imageFiles['tmp_name'][0] ?? '',
        'error' => $imageFiles['error'][0] ?? UPLOAD_ERR_NO_FILE,
        'size' => $imageFiles['size'][0] ?? 0
    ];
}else{
    $firstFile = $imageFiles;
}

if($firstFile['error'] !== UPLOAD_ERR_OK){
    jsonResponse(false, 'Product image is required', [], 422);
}

$allowedTypes = [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/webp'
];

$fileType = mime_content_type($firstFile['tmp_name']);

if(!in_array($fileType, $allowedTypes)){
    jsonResponse(false, 'Only JPG, PNG and WEBP images are allowed', [], 422);
}

$maxSize = 2 * 1024 * 1024;

if($firstFile['size'] > $maxSize){
    jsonResponse(false, 'Image size must be less than 2MB', [], 422);
}

$uploadDir = __DIR__ . '/../../uploads/products/';

if(!is_dir($uploadDir)){
    mkdir($uploadDir, 0777, true);
}

$extension = pathinfo($firstFile['name'], PATHINFO_EXTENSION);

$fileName =
    time() .
    '_' .
    uniqid() .
    '.' .
    strtolower($extension);

$uploadPath = $uploadDir . $fileName;

if(!move_uploaded_file($firstFile['tmp_name'], $uploadPath)){
    jsonResponse(false, 'Image upload failed', [], 500);
}

try{

    $database = new Database();
    $db = $database->connect();

    $query = "
        INSERT INTO products
        (
            name,
            description,
            price,
            image,
            category_id
        )
        VALUES
        (
            :name,
            :description,
            :price,
            :image,
            :category_id
        )
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':name' => $name,
        ':description' => $description,
        ':price' => $price,
        ':image' => $fileName,
        ':category_id' => $categoryId !== '' ? $categoryId : null
    ]);

    $productId = $db->lastInsertId();

    try{
        $db->exec("CREATE TABLE IF NOT EXISTS product_images (id INT AUTO_INCREMENT PRIMARY KEY, product_id INT NOT NULL, image VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

        if(is_array($imageFiles['name'])){
            for($i = 1; $i < count($imageFiles['name']); $i++){
                if(($imageFiles['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK){ continue; }
                if(($imageFiles['size'][$i] ?? 0) > $maxSize){ continue; }
                $type = mime_content_type($imageFiles['tmp_name'][$i]);
                if(!in_array($type, $allowedTypes)){ continue; }
                $ext = pathinfo($imageFiles['name'][$i], PATHINFO_EXTENSION);
                $extraName = time() . '_' . uniqid() . '.' . strtolower($ext);
                if(move_uploaded_file($imageFiles['tmp_name'][$i], $uploadDir . $extraName)){
                    $imgStmt = $db->prepare("INSERT INTO product_images (product_id, image) VALUES (:product_id, :image)");
                    $imgStmt->execute([':product_id' => $productId, ':image' => $extraName]);
                }
            }
        }
    }catch(Exception $e){}

    jsonResponse(
        true,
        'Product created successfully',
        [
            'product_id' => $productId,
            'image' => $fileName
        ]
    );

}catch(Exception $e){

    if(file_exists($uploadPath)){
        unlink($uploadPath);
    }

    jsonResponse(false, 'Product create failed', [], 500);
}   