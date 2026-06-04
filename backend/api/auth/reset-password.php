<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

$data = json_decode(file_get_contents("php://input"), true);

$token = trim($data['token'] ?? '');
$newPassword = trim($data['new_password'] ?? '');
$confirmPassword = trim($data['confirm_password'] ?? '');

if ($token === '' || $newPassword === '' || $confirmPassword === '') {
    jsonResponse(false, 'All fields are required');
    exit;
}

if (strlen($newPassword) < 6) {
    jsonResponse(false, 'Password must be at least 6 characters');
    exit;
}

if ($newPassword !== $confirmPassword) {
    jsonResponse(false, 'Passwords do not match');
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $stmt = $db->prepare("
        SELECT id
        FROM users
        WHERE reset_token = :token
        AND token_expiry >= NOW()
        LIMIT 1
    ");

    $stmt->execute([
        ':token' => $token
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        jsonResponse(false, 'Invalid or expired reset link');
        exit;
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    $updateStmt = $db->prepare("
        UPDATE users
        SET password = :password,
            reset_token = NULL,
            token_expiry = NULL
        WHERE id = :id
    ");

    $updateStmt->execute([
        ':password' => $hashedPassword,
        ':id' => $user['id']
    ]);

    jsonResponse(true, 'Password reset successfully');

} catch (Exception $e) {
    jsonResponse(false, 'Password reset failed', null, 500);
}