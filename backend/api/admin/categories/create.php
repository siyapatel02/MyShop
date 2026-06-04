<?php

require_once __DIR__ . '/../../../config/cors.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../helpers/response.php';
require_once __DIR__ . '/../../../helpers/jwt.php';

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

function makeSlug($text){
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
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

$data = json_decode(file_get_contents("php://input"), true);

if(!is_array($data)){
    jsonResponse(false, 'Invalid JSON body', [], 400);
}

$allowedFields = ['name', 'parent_id', 'status'];

foreach($data as $key => $value){
    if(!in_array($key, $allowedFields)){
        jsonResponse(false, 'Invalid field: ' . $key, [], 422);
    }
}

$name = trim($data['name'] ?? '');
$parentId = $data['parent_id'] ?? null;
$status = trim($data['status'] ?? 'active');

if($name === ''){
    jsonResponse(false, 'Category name is required', [], 422);
}

if($parentId !== null && $parentId !== '' && (!is_numeric($parentId) || $parentId <= 0)){
    jsonResponse(false, 'Invalid parent category', [], 422);
}

if(!in_array($status, ['active', 'inactive'])){
    jsonResponse(false, 'Invalid status', [], 422);
}

$slug = makeSlug($name);

try{

    $database = new Database();
    $db = $database->connect();

    if($parentId !== null && $parentId !== ''){

        $parentQuery = "
            SELECT id
            FROM categories
            WHERE id = :id
            LIMIT 1
        ";

        $parentStmt = $db->prepare($parentQuery);
        $parentStmt->execute([
            ':id' => $parentId
        ]);

        if(!$parentStmt->fetch(PDO::FETCH_ASSOC)){
            jsonResponse(false, 'Parent category not found', [], 404);
        }
    }

    $checkQuery = "
        SELECT id
        FROM categories
        WHERE slug = :slug
        LIMIT 1
    ";

    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([
        ':slug' => $slug
    ]);

    if($checkStmt->fetch(PDO::FETCH_ASSOC)){
        jsonResponse(false, 'Category already exists', [], 409);
    }

    $query = "
        INSERT INTO categories
        (
            name,
            slug,
            parent_id,
            status
        )
        VALUES
        (
            :name,
            :slug,
            :parent_id,
            :status
        )
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':name' => $name,
        ':slug' => $slug,
        ':parent_id' => $parentId !== '' ? $parentId : null,
        ':status' => $status
    ]);

    jsonResponse(
        true,
        'Category created successfully',
        [
            'category_id' => $db->lastInsertId(),
            'slug' => $slug
        ],
        201
    );

}catch(Exception $e){

    jsonResponse(false, 'Category create failed', [], 500);
}