<?php

require_once __DIR__ . '/../../config/cors.php';

require_once __DIR__ . '/../../config/database.php';

require_once __DIR__ . '/../../helpers/response.php';

require_once __DIR__ . '/../../models/Admin.php';

/*
|--------------------------------------------------------------------------
| GET JSON DATA
|--------------------------------------------------------------------------
*/

$data = json_decode(

    file_get_contents("php://input"),

    true
);

/*
|--------------------------------------------------------------------------
| INPUTS
|--------------------------------------------------------------------------
*/

$username =
$data['username'] ?? '';

$email =
$data['email'] ?? '';

$password =
$data['password'] ?? '';

/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if(

    empty($username)

    ||

    empty($email)

    ||

    empty($password)
){

    jsonResponse(
        false,
        'All fields required'
    );
}

/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

$database = new Database();

$db = $database->connect();

$adminModel =
new Admin($db);

/*
|--------------------------------------------------------------------------
| CHECK EXISTING ADMIN
|--------------------------------------------------------------------------
*/

$existing =
$adminModel->findByEmail(
    $email
);

if($existing){

    jsonResponse(
        false,
        'Email already exists'
    );
}

/*
|--------------------------------------------------------------------------
| HASH PASSWORD
|--------------------------------------------------------------------------
*/

$hashedPassword =
password_hash(

    $password,

    PASSWORD_BCRYPT
);

/*
|--------------------------------------------------------------------------
| INSERT ADMIN
|--------------------------------------------------------------------------
*/

$query =

"INSERT INTO admins

(

    username,
    email,
    password,
    created_at

)

VALUES

(

    :username,
    :email,
    :password,
    NOW()

)";

$stmt =
$db->prepare($query);

$stmt->bindParam(
    ':username',
    $username
);

$stmt->bindParam(
    ':email',
    $email
);

$stmt->bindParam(
    ':password',
    $hashedPassword
);

$success =
$stmt->execute();

/*
|--------------------------------------------------------------------------
| RESPONSE
|--------------------------------------------------------------------------
*/

if($success){

    jsonResponse(
        true,
        'Admin registered successfully'
    );
}

jsonResponse(
    false,
    'Registration failed'
);