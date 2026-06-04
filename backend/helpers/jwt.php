<?php

/*
|--------------------------------------------------------------------------
| JWT SECRET KEY
|--------------------------------------------------------------------------
*/

$secret_key = "myshop_secret_key_123";

/*
|--------------------------------------------------------------------------
| GENERATE JWT
|--------------------------------------------------------------------------
*/
function generateJWT($payload){

    global $secret_key;

    $payload['iat'] = time();

    $payload['exp'] = time() + (60 * 60 * 24);
    // 24 hours

    $header = json_encode([

        'typ' => 'JWT',

        'alg' => 'HS256'
    ]);

    $header = base64_encode($header);

    $payload = base64_encode(

        json_encode($payload)
    );

    $signature = hash_hmac(

        'sha256',

        $header . "." . $payload,

        $secret_key,

        true
    );

    $signature =
    base64_encode($signature);

    return

    $header . "." .

    $payload . "." .

    $signature;
}

/*
|--------------------------------------------------------------------------
| VERIFY JWT
|--------------------------------------------------------------------------
*/

function verifyToken($token){

    global $secret_key;

    $tokenParts =
    explode('.', $token);

    if(count($tokenParts) != 3){

        return false;
    }

    $header =
    $tokenParts[0];

    $payload =
    $tokenParts[1];

    $signature =
    $tokenParts[2];

    $validSignature =
    base64_encode(

        hash_hmac(

            'sha256',

            $header . "." . $payload,

            $secret_key,

            true
        )
    );

    if($signature !== $validSignature){

        return false;
    }

    $decodedPayload = json_decode(

        base64_decode($payload),

        true
    );

    if(!$decodedPayload){

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | TOKEN EXPIRY CHECK
    |--------------------------------------------------------------------------
    */

    if(

        isset($decodedPayload['exp'])

        &&

        time() > $decodedPayload['exp']
    ){

        return false;
    }

    return $decodedPayload;
}