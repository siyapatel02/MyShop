<?php

require_once __DIR__ . '/../helpers/jwt.php';
require_once __DIR__ . '/../helpers/response.php';

$headers = getallheaders();

$authHeader =
    $headers['Authorization']
    ?? $headers['authorization']
    ?? '';

if (!$authHeader) {
    jsonResponse(false, 'Unauthorized', null, 401);
    exit;
}

$token = str_replace('Bearer ', '', $authHeader);

$user = verifyToken($token);

if (!$user) {
    jsonResponse(false, 'Invalid token', null, 401);
    exit;
}

if (($user['role'] ?? '') !== 'admin') {
    jsonResponse(false, 'Admin access required', null, 403);
    exit;
}