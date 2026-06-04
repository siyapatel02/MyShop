<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

function sendMail($toEmail, $toName, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();

        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        /*
        |--------------------------------------------------------------------------
        | YOUR GMAIL SMTP DETAILS
        |--------------------------------------------------------------------------
        */

        $mail->Username = 'siyaptl02@gmail.com';

        /*
        | Use Google App Password here.
        | Do not use normal Gmail password.
        */
        $mail->Password = 'elmg opmh efei tvbv';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        /*
        |--------------------------------------------------------------------------
        | SENDER
        |--------------------------------------------------------------------------
        */

        $mail->setFrom(
            'siyaptl02@gmail.com',
            'MyShop'
        );

        /*
        |--------------------------------------------------------------------------
        | RECEIVER
        |--------------------------------------------------------------------------
        */

        $mail->addAddress(
            $toEmail,
            $toName
        );

        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();

        return true;

    } catch (Exception $e) {

        return false;
    }
}