<?php

require_once
__DIR__ .
'/../helpers/jwt.php';

class AuthService {

    /*
    |--------------------------------------------------------------------------
    | GENERATE LOGIN RESPONSE
    |--------------------------------------------------------------------------
    */

    public static function loginResponse($user){

        $token =
        generateToken($user);

        return [

            'token' => $token,

            'user' => [

                'id' => $user['id'],

                'name' => $user['name'],

                'email' => $user['email']
            ]
        ];
    }
}