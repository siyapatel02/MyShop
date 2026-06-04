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

$name = trim($data['name'] ?? '');
$phone = trim($data['phone'] ?? '');
$dob = trim($data['dob'] ?? '');
$gender = trim($data['gender'] ?? '');

if (empty($name)) {
    jsonResponse(false, 'Name is required');
    exit;
}

if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
    jsonResponse(false, 'Phone number must be 10 digits');
    exit;
}

$allowedGender = ['', 'male', 'female', 'other', 'prefer-not-to-say'];

if (!in_array($gender, $allowedGender)) {
    jsonResponse(false, 'Invalid gender');
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $query = "
        UPDATE users
        SET 
            name = :name,
            phone = :phone,
            dob = :dob,
            gender = :gender
        WHERE id = :id
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':name' => $name,
        ':phone' => $phone,
        ':dob' => $dob ?: null,
        ':gender' => $gender,
        ':id' => $user['id']
    ]);

    jsonResponse(true, 'Profile updated successfully');

} catch (Exception $e) {
    jsonResponse(false, 'Failed to update profile: ' . $e->getMessage(), null, 500);
}