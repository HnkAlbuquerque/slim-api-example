<?php

namespace App\Repositories;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailerRepository
{
    public function send($email, $user, $body, $companyName): String
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAILER_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAILER_USERNAME'];
            $mail->Password   = $_ENV['MAILER_PASSWORD'];
            $mail->SMTPSecure = $_ENV['MAILER_SECURITY'];
            $mail->Port       = $_ENV['MAILER_PORT'];

            //Recipients
            $mail->setFrom($_ENV['MAILER_USERNAME'], 'Financial Market Data');
            $mail->addAddress($email,$user);

            //Content
            $mail->isHTML(false);
            $mail->Subject = 'Your requested market data from ' . $companyName;
            $mail->Body = $body;

            $mail->send();

            $message = 'Message has been sent';
        } catch (Exception $e) {
            $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        return $message;
    }
}

