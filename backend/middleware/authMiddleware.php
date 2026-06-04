<?php

require_once
__DIR__ .
'/../helpers/jwt.php';

require_once
__DIR__ .
'/../helpers/response.php';

$headers = getallheaders();

if(
    !isset(
        $headers['Authorization']
    )
){

    jsonResponse(

        false,

        'Unauthorized',

        [],

        401
    );
}

$token = str_replace(

    'Bearer ',

    '',

    $headers['Authorization']
);

$user = verifyToken($token);

if(!$user){

    jsonResponse(

        false,

        'Invalid Token',

        [],

        401
    );
}