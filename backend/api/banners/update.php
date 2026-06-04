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

$id = intval($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$subtitle = trim($_POST['subtitle'] ?? '');
$buttonText = trim($_POST['button_text'] ?? '');
$buttonLink = trim($_POST['button_link'] ?? '');
$status = trim($_POST['status'] ?? 'active');

if ($id <= 0) {
    jsonResponse(false, 'Banner id is required');
    exit;
}

if (empty($title)) {
    jsonResponse(false, 'Title is required');
    exit;
}

if (!in_array($status, ['active', 'inactive'])) {
    jsonResponse(false, 'Invalid status');
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $selectQuery = "
        SELECT image
        FROM banners
        WHERE id = :id
        LIMIT 1
    ";

    $selectStmt = $db->prepare($selectQuery);
    $selectStmt->execute([
        ':id' => $id
    ]);

    $oldBanner = $selectStmt->fetch(PDO::FETCH_ASSOC);

    if (!$oldBanner) {
        jsonResponse(false, 'Banner not found');
        exit;
    }

    $imageName = $oldBanner['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
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

        $newImageName = time() . '_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $newImageName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            jsonResponse(false, 'Image upload failed');
            exit;
        }

        $oldImagePath = $uploadDir . $oldBanner['image'];

        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }

        $imageName = $newImageName;
    }

    $query = "
        UPDATE banners
        SET
            title = :title,
            subtitle = :subtitle,
            image = :image,
            button_text = :button_text,
            button_link = :button_link,
            status = :status
        WHERE id = :id
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':title' => $title,
        ':subtitle' => $subtitle,
        ':image' => $imageName,
        ':button_text' => $buttonText,
        ':button_link' => $buttonLink,
        ':status' => $status,
        ':id' => $id
    ]);

    jsonResponse(true, 'Banner updated successfully', [
        'image' => $imageName
    ]);

} catch (Exception $e) {
    jsonResponse(false, 'Banner update failed: ' . $e->getMessage(), null, 500);
}