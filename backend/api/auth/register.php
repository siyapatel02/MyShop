<?php

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/mailer.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    jsonResponse(false, 'Only POST method allowed', [], 405);
}

$data = json_decode(file_get_contents("php://input"), true);

if(!is_array($data)){
    jsonResponse(false, 'Invalid JSON body', [], 400);
}

$allowedFields = ['name', 'email', 'password'];

foreach($data as $key => $value){
    if(!in_array($key, $allowedFields)){
        jsonResponse(false, 'Invalid field: ' . $key, [], 422);
    }
}

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

if($name === '' || $email === '' || $password === ''){
    jsonResponse(false, 'All fields are required', [], 422);
}

if(strlen($name) < 2){
    jsonResponse(false, 'Name must be at least 2 characters', [], 422);
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    jsonResponse(false, 'Invalid email format', [], 422);
}

if(strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)){
    jsonResponse(false, 'Password must be at least 8 characters and include uppercase, lowercase, number and special symbol', [], 422);
}

try{

    $database = new Database();
    $db = $database->connect();

    $checkQuery = "
        SELECT id
        FROM users
        WHERE email = :email
        LIMIT 1
    ";

    $checkStmt = $db->prepare($checkQuery);

    $checkStmt->execute([
        ':email' => $email
    ]);

    if($checkStmt->fetch(PDO::FETCH_ASSOC)){
        jsonResponse(false, 'Email already exists', [], 409);
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $verificationToken = bin2hex(random_bytes(32));

    $query = "
        INSERT INTO users
        (
            name,
            email,
            password,
            email_verified,
            email_verification_token
        )
        VALUES
        (
            :name,
            :email,
            :password,
            0,
            :email_verification_token
        )
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':password' => $hashedPassword,
        ':email_verification_token' => $verificationToken
    ]);

    $userId = $db->lastInsertId();

    $verifyLink =
        'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/verify-email.php?token=' .
        $verificationToken;

    $emailBody = "
        <h2>Welcome to MyShop</h2>

        <p>Hello <strong>{$name}</strong>,</p>

        <p>Thank you for registering with MyShop.</p>

        <p>Please verify your email by clicking the button below:</p>

        <p>
            <a href='{$verifyLink}'
               style='background:#111;color:#fff;padding:10px 18px;text-decoration:none;border-radius:5px;display:inline-block;'>
                Verify Email
            </a>
        </p>

        <p>If the button does not work, copy and open this link:</p>

        <p>{$verifyLink}</p>
    ";

    $mailSent = sendMail(
        $email,
        $name,
        'Verify your MyShop email',
        $emailBody
    );

    jsonResponse(
        true,
        $mailSent
            ? 'Registration successful. Verification email sent.'
            : 'Registration successful, but verification email could not be sent.',
        [
            'user_id' => $userId,
            'email_verified' => 0,
            'mail_sent' => $mailSent
        ],
        201
    );

}catch(Exception $e){

    jsonResponse(false, 'Registration failed: ' . $e->getMessage(), [], 500);
}