<?php

function jsonResponse(

    $status,

    $message,

    $data = [],

    $statusCode = 200
){

    http_response_code($statusCode);

    echo json_encode([

        'status' => $status,

        'message' => $message,

        'data' => $data
    ]);

    exit();
}