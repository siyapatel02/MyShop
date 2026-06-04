<?php

require_once __DIR__ . '/../../config/cors.php';

require_once __DIR__ . '/../../config/database.php';

require_once __DIR__ . '/../../helpers/response.php';

require_once __DIR__ . '/../../helpers/jwt.php';

require_once __DIR__ . '/../../models/Admin.php';

/*
|--------------------------------------------------------------------------
| GET RAW JSON DATA
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
| FIND ADMIN
|--------------------------------------------------------------------------
*/

$admin =
$adminModel->findByEmail(
    $email
);

if(!$admin){

    jsonResponse(
        false,
        'Admin not found'
    );
}

/*
|--------------------------------------------------------------------------
| VERIFY PASSWORD
|--------------------------------------------------------------------------
*/

if(

    !password_verify(

        $password,

        $admin['password']
    )
){

    jsonResponse(
        false,
        'Invalid credentials'
    );
}

/*
|--------------------------------------------------------------------------
| JWT PAYLOAD
|--------------------------------------------------------------------------
*/

$payload = [

    'id' => $admin['id'],

    'email' => $admin['email'],

    'role' => 'admin'
];

/*
|--------------------------------------------------------------------------
| GENERATE TOKEN
|--------------------------------------------------------------------------
*/

$token =
generateJWT($payload);

/*
|--------------------------------------------------------------------------
| RESPONSE
|--------------------------------------------------------------------------
*/

jsonResponse(

    true,

    'Admin login successful',

    [

        'token' => $token,

        'admin' => [

            'id' =>
            $admin['id'],

            'username' =>
            $admin['username'],

            'email' =>
            $admin['email']
        ]
    ]
);