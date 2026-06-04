<?php

define(
    'BASE_URL',
    'http://' . $_SERVER['HTTP_HOST'] . '/' . (explode('/', trim($_SERVER['SCRIPT_NAME'], '/'))[0] ?? '') . '/'
);

define(
    'API_URL',
    BASE_URL .
    'backend/api/'
);

define(
    'UPLOAD_URL',
    BASE_URL .
    'backend/uploads/'
);

define(
    'JWT_SECRET',
    'myshop_super_secret_key'
);