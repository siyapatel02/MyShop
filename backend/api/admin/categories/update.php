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

$allowedFields = ['id', 'name', 'parent_id', 'status'];

foreach($data as $key => $value){
    if(!in_array($key, $allowedFields)){
        jsonResponse(false, 'Invalid field: ' . $key, [], 422);
    }
}

$id = (int)($data['id'] ?? 0);
$name = trim($data['name'] ?? '');
$parentId = $data['parent_id'] ?? null;
$status = trim($data['status'] ?? 'active');

if($id <= 0){
    jsonResponse(false, 'Category ID required', [], 422);
}

if($name === ''){
    jsonResponse(false, 'Category name is required', [], 422);
}

if($parentId !== null && $parentId !== '' && (!is_numeric($parentId) || $parentId <= 0)){
    jsonResponse(false, 'Invalid parent category', [], 422);
}

if((int)$parentId === $id){
    jsonResponse(false, 'Category cannot be its own parent', [], 422);
}

if(!in_array($status, ['active', 'inactive'])){
    jsonResponse(false, 'Invalid status', [], 422);
}

$slug = makeSlug($name);

try{

    $database = new Database();
    $db = $database->connect();

    $findQuery = "
        SELECT id
        FROM categories
        WHERE id = :id
        LIMIT 1
    ";

    $findStmt = $db->prepare($findQuery);
    $findStmt->execute([
        ':id' => $id
    ]);

    if(!$findStmt->fetch(PDO::FETCH_ASSOC)){
        jsonResponse(false, 'Category not found', [], 404);
    }

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

    $duplicateQuery = "
        SELECT id
        FROM categories
        WHERE slug = :slug
        AND id != :id
        LIMIT 1
    ";

    $duplicateStmt = $db->prepare($duplicateQuery);
    $duplicateStmt->execute([
        ':slug' => $slug,
        ':id' => $id
    ]);

    if($duplicateStmt->fetch(PDO::FETCH_ASSOC)){
        jsonResponse(false, 'Category already exists', [], 409);
    }

    $query = "
        UPDATE categories
        SET
            name = :name,
            slug = :slug,
            parent_id = :parent_id,
            status = :status
        WHERE id = :id
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':name' => $name,
        ':slug' => $slug,
        ':parent_id' => $parentId !== '' ? $parentId : null,
        ':status' => $status,
        ':id' => $id
    ]);

    jsonResponse(
        true,
        'Category updated successfully',
        [
            'category_id' => $id,
            'slug' => $slug
        ]
    );

}catch(Exception $e){

    jsonResponse(false, 'Category update failed', [], 500);
}