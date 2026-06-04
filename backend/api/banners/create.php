<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/jwt.php';

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$authHeader) {
    jsonResponse(false, 'Token missing', null, 401);
    exit;
}

$token = str_replace('Bearer ', '', $authHeader);
$admin = verifyToken($token);

if (!$admin) {
    jsonResponse(false, 'Invalid token', null, 401);
    exit;
}

$title = trim($_POST['title'] ?? '');
$subtitle = trim($_POST['subtitle'] ?? '');
$buttonText = trim($_POST['button_text'] ?? '');
$buttonLink = trim($_POST['button_link'] ?? '');
$status = trim($_POST['status'] ?? 'active');

if (empty($title)) {
    jsonResponse(false, 'Title is required');
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
    jsonResponse(false, 'Banner image is required');
    exit;
}

if (!in_array($status, ['active', 'inactive'])) {
    jsonResponse(false, 'Invalid status');
    exit;
}

$uploadDir = __DIR__ . '/../../uploads/banners/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
$originalName = $_FILES['image']['name'];
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedTypes)) {
    jsonResponse(false, 'Only JPG, JPEG, PNG, WEBP images are allowed');
    exit;
}

$imageName = time() . '_' . uniqid() . '.' . $extension;
$targetPath = $uploadDir . $imageName;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
    jsonResponse(false, 'Image upload failed');
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $query = "
        INSERT INTO banners
        (
            title,
            subtitle,
            image,
            button_text,
            button_link,
            status
        )
        VALUES
        (
            :title,
            :subtitle,
            :image,
            :button_text,
            :button_link,
            :status
        )
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':title' => $title,
        ':subtitle' => $subtitle,
        ':image' => $imageName,
        ':button_text' => $buttonText,
        ':button_link' => $buttonLink,
        ':status' => $status
    ]);

    jsonResponse(true, 'Banner created successfully', [
        'banner_id' => $db->lastInsertId(),
        'image' => $imageName
    ]);

} catch (Exception $e) {
    if (file_exists($targetPath)) {
        unlink($targetPath);
    }

    jsonResponse(false, 'Banner create failed: ' . $e->getMessage(), null, 500);
}