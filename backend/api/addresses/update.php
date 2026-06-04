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

if (!$user) {
    jsonResponse(false, 'Invalid token', null, 401);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    jsonResponse(false, 'Invalid JSON data');
    exit;
}

$id = intval($data['id'] ?? 0);
$fullname = trim($data['fullname'] ?? '');
$phone = trim($data['phone'] ?? '');
$pincode = trim($data['pincode'] ?? '');
$addressLine = trim($data['address_line'] ?? '');
$city = trim($data['city'] ?? '');
$state = trim($data['state'] ?? '');
$country = trim($data['country'] ?? 'India');
$addressType = trim($data['address_type'] ?? 'home');
$label = trim($data['label'] ?? $addressType);

if (
    $id <= 0 ||
    empty($fullname) ||
    empty($phone) ||
    empty($pincode) ||
    empty($addressLine) ||
    empty($city) ||
    empty($state)
) {
    jsonResponse(false, 'All fields are required');
    exit;
}

if (!preg_match('/^[0-9]{10}$/', $phone)) {
    jsonResponse(false, 'Phone number must be 10 digits');
    exit;
}

if (!preg_match('/^[0-9]{6}$/', $pincode)) {
    jsonResponse(false, 'Pincode must be 6 digits');
    exit;
}

try {

    $database = new Database();
    $db = $database->connect();

    $query = "
        UPDATE user_addresses
        SET
            fullname = :fullname,
            phone = :phone,
            pincode = :pincode,
            address_line = :address_line,
            city = :city,
            state = :state,
            country = :country,
            address_type = :address_type,
            label = :label
        WHERE id = :id
        AND user_id = :user_id
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':fullname' => $fullname,
        ':phone' => $phone,
        ':pincode' => $pincode,
        ':address_line' => $addressLine,
        ':city' => $city,
        ':state' => $state,
        ':country' => $country,
        ':address_type' => $addressType,
        ':label' => $label,
        ':id' => $id,
        ':user_id' => $user['id']
    ]);

    if ($stmt->rowCount() <= 0) {
        jsonResponse(false, 'Address not found or no changes made');
        exit;
    }

    jsonResponse(true, 'Address updated successfully');

} catch (Exception $e) {
    jsonResponse(false, 'Address update failed: ' . $e->getMessage(), null, 500);
}