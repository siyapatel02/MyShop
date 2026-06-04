<?php

require_once __DIR__ . '/../helpers/mailer.php';

$result = sendMail(
    'YOUR_RECEIVER_EMAIL@gmail.com',
    'Test User',
    'MyShop Test Mail',
    '<h2>PHPMailer is working</h2><p>This is a test email.</p>'
);

if ($result) {
    echo 'Mail sent successfully';
} else {
    echo 'Mail failed';
}