<?php

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

$token = trim($_GET['token'] ?? '');

if ($token === '') {
    echo "<h2>Invalid verification link</h2>";
    exit;
}

try {

    $database = new Database();
    $db = $database->connect();

    $query = "
        SELECT id, email_verified
        FROM users
        WHERE email_verification_token = :token
        LIMIT 1
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':token' => $token
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<h2>Invalid or expired verification link</h2>";
        exit;
    }

    if ((int)$user['email_verified'] === 1) {
        echo "<h2>Email already verified</h2>";
        echo "<p>You can now login and place orders.</p>";
        exit;
    }

    $updateQuery = "
        UPDATE users
        SET
            email_verified = 1,
            email_verified_at = NOW(),
            email_verification_token = NULL
        WHERE id = :id
    ";

    $updateStmt = $db->prepare($updateQuery);

    $updateStmt->execute([
        ':id' => $user['id']
    ]);

    echo "<h2>Email verified successfully</h2>";
    echo "<p>You can now login and place orders.</p>";

} catch (Exception $e) {

    echo "<h2>Email verification failed</h2>";
}