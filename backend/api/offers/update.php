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

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    jsonResponse(false, 'Invalid JSON data');
    exit;
}

$id = intval($data['id'] ?? 0);
$code = strtoupper(trim($data['code'] ?? ''));
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$discountType = trim($data['discount_type'] ?? '');
$discountValue = floatval($data['discount_value'] ?? 0);
$minimumOrder = floatval($data['minimum_order'] ?? 0);
$maxDiscount = $data['max_discount'] === '' ? null : ($data['max_discount'] ?? null);
$startDate = $data['start_date'] ?: null;
$expiryDate = $data['expiry_date'] ?: null;
$status = trim($data['status'] ?? 'active');
$firstOrderOnly = intval($data['first_order_only'] ?? 0);

if (
    $id <= 0 ||
    empty($code) ||
    empty($title) ||
    empty($discountType) ||
    $discountValue <= 0
) {
    jsonResponse(false, 'Required fields are missing');
    exit;
}

if (!in_array($discountType, ['percentage', 'fixed'])) {
    jsonResponse(false, 'Invalid discount type');
    exit;
}

if (!in_array($status, ['active', 'inactive'])) {
    jsonResponse(false, 'Invalid status');
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $checkQuery = "
        SELECT id 
        FROM offers 
        WHERE code = :code
        AND id != :id
        LIMIT 1
    ";

    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([
        ':code' => $code,
        ':id' => $id
    ]);

    if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
        jsonResponse(false, 'Offer code already exists');
        exit;
    }

    $query = "
        UPDATE offers
        SET
            code = :code,
            title = :title,
            description = :description,
            discount_type = :discount_type,
            discount_value = :discount_value,
            minimum_order = :minimum_order,
            max_discount = :max_discount,
            first_order_only = :first_order_only,
            start_date = :start_date,
            expiry_date = :expiry_date,
            status = :status
        WHERE id = :id
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':code' => $code,
        ':title' => $title,
        ':description' => $description,
        ':discount_type' => $discountType,
        ':discount_value' => $discountValue,
        ':minimum_order' => $minimumOrder,
        ':max_discount' => $maxDiscount,
        ':first_order_only' => $firstOrderOnly,
        ':start_date' => $startDate,
        ':expiry_date' => $expiryDate,
        ':status' => $status,
        ':id' => $id
    ]);

    jsonResponse(true, 'Offer updated successfully');

} catch (Exception $e) {
    jsonResponse(false, 'Offer update failed: ' . $e->getMessage(), null, 500);
}