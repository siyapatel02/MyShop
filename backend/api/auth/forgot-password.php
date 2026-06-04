<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/mailer.php';

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');

if ($email === '') {
    jsonResponse(false, 'Email is required');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Invalid email format');
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $stmt = $db->prepare("
        SELECT id, name, email, email_verified
        FROM users
        WHERE email = :email
        LIMIT 1
    ");

    $stmt->execute([
        ':email' => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        jsonResponse(false, 'Email not found');
        exit;
    }

    if ((int)$user['email_verified'] !== 1) {
        jsonResponse(false, 'Please verify your email before resetting password');
        exit;
    }

    $resetToken = bin2hex(random_bytes(32));
    $tokenExpiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));

    $updateStmt = $db->prepare("
        UPDATE users
        SET reset_token = :reset_token,
            token_expiry = :token_expiry
        WHERE id = :id
    ");

    $updateStmt->execute([
        ':reset_token' => $resetToken,
        ':token_expiry' => $tokenExpiry,
        ':id' => $user['id']
    ]);

    $projectBase = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'))[0] ?? '';
    $resetLink =
        'http://' . $_SERVER['HTTP_HOST'] . '/' . $projectBase . '/frontend/pages/reset-password.php?token=' .
        $resetToken;

    $body = "
        <h2>Password Reset Request</h2>
        <p>Hello <strong>{$user['name']}</strong>,</p>
        <p>Click below to reset your password. This link is valid for 30 minutes.</p>

        <p>
            <a href='{$resetLink}'
               style='background:#111;color:#fff;padding:10px 18px;text-decoration:none;border-radius:5px;display:inline-block;'>
                Reset Password
            </a>
        </p>

        <p>If button does not work, copy this link:</p>
        <p>{$resetLink}</p>
    ";

    $mailSent = sendMail(
        $user['email'],
        $user['name'],
        'Reset your MyShop password',
        $body
    );

    if (!$mailSent) {
        jsonResponse(false, 'Failed to send reset email');
        exit;
    }

    jsonResponse(true, 'Password reset link sent to your email');

} catch (Exception $e) {
    jsonResponse(false, 'Forgot password failed: ' . $e->getMessage(), null, 500);
}