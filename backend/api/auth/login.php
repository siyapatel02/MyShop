<?php

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/jwt.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    jsonResponse(false, 'Only POST method allowed', [], 405);
}

$data = json_decode(file_get_contents("php://input"), true);

if(!is_array($data)){
    jsonResponse(false, 'Invalid JSON body', [], 400);
}

$allowedFields = ['email', 'password'];

foreach($data as $key => $value){
    if(!in_array($key, $allowedFields)){
        jsonResponse(false, 'Invalid field: ' . $key, [], 422);
    }
}

$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

if($email === '' || $password === ''){
    jsonResponse(false, 'Email and password are required', [], 422);
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    jsonResponse(false, 'Invalid email format', [], 422);
}

try{

    $database = new Database();
    $db = $database->connect();

    $query = "
        SELECT
            id,
            name,
            email,
            password,
            status
        FROM users
        WHERE email = :email
        LIMIT 1
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        ':email' => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$user){
        jsonResponse(false, 'Invalid email or password', [], 401);
    }

    if(!password_verify($password, $user['password'])){
        jsonResponse(false, 'Invalid email or password', [], 401);
    }

    if(
        isset($user['status'])
        &&
        $user['status'] === 'blocked'
    ){
        jsonResponse(
            false,
            'Your account has been blocked. Please contact support.',
            [],
            403
        );
    }

    $payload = [
        'id' => $user['id'],
        'email' => $user['email'],
        'role' => 'user'
    ];

    $token = generateJWT($payload);

    jsonResponse(
        true,
        'Login successful',
        [
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ]
        ]
    );

}catch(Exception $e){

    jsonResponse(false, 'Login failed', [], 500);
}