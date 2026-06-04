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
$user = verifyToken($token);

if (!$user || empty($user['id'])) {
    jsonResponse(false, 'Invalid token', null, 401);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    jsonResponse(false, 'Invalid JSON data');
    exit;
}

$code = strtoupper(trim($data['code'] ?? ''));
$subtotal = floatval($data['subtotal'] ?? 0);

if (empty($code)) {
    jsonResponse(false, 'Coupon code is required');
    exit;
}

if ($subtotal <= 0) {
    jsonResponse(false, 'Invalid subtotal');
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $query = "
        SELECT *
        FROM offers
        WHERE code = :code
        LIMIT 1
    ";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':code' => $code
    ]);

    $offer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$offer) {
        jsonResponse(false, 'Invalid coupon code');
        exit;
    }

    if ($offer['status'] !== 'active') {
        jsonResponse(false, 'This coupon is inactive');
        exit;
    }

    $today = date('Y-m-d');

    if (!empty($offer['start_date']) && $today < $offer['start_date']) {
        jsonResponse(false, 'This coupon is not started yet');
        exit;
    }

    if (!empty($offer['expiry_date']) && $today > $offer['expiry_date']) {
        jsonResponse(false, 'This coupon has expired');
        exit;
    }

    if ($subtotal < floatval($offer['minimum_order'])) {
        jsonResponse(
            false,
            'Minimum order amount should be ₹' . number_format($offer['minimum_order'], 2)
        );
        exit;
    }
    if (intval($offer['first_order_only']) === 1) {

        $orderCheckQuery = "
            SELECT COUNT(*) AS total_orders
            FROM orders
            WHERE user_id = :user_id
        ";

        $orderCheckStmt = $db->prepare($orderCheckQuery);

        $orderCheckStmt->execute([
            ':user_id' => $user['id']
        ]);

        $orderCheck =
        $orderCheckStmt->fetch(PDO::FETCH_ASSOC);

        if (
            $orderCheck
            &&
            intval($orderCheck['total_orders']) > 0
        ) {
            jsonResponse(
                false,
                'This coupon is valid only for your first order'
            );
            exit;
        }
    }

    $discount = 0;

    if ($offer['discount_type'] === 'percentage') {
        $discount = ($subtotal * floatval($offer['discount_value'])) / 100;

        if (!empty($offer['max_discount']) && $discount > floatval($offer['max_discount'])) {
            $discount = floatval($offer['max_discount']);
        }
    } else {
        $discount = floatval($offer['discount_value']);
    }

    if ($discount > $subtotal) {
        $discount = $subtotal;
    }

    $newSubtotal = $subtotal - $discount;

    jsonResponse(true, 'Coupon applied successfully', [
        'offer_id' => $offer['id'],
        'code' => $offer['code'],
        'title' => $offer['title'],
        'discount_type' => $offer['discount_type'],
        'discount_value' => floatval($offer['discount_value']),
        'discount_amount' => round($discount, 2),
        'subtotal' => round($subtotal, 2),
        'new_subtotal' => round($newSubtotal, 2)
    ]);

} catch (Exception $e) {
    jsonResponse(false, 'Coupon apply failed: ' . $e->getMessage(), null, 500);
}