<?php
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

/* 🛑 BOT BLOCK (HONEYPOT) */
if (!empty($_POST['company'])) {
    echo json_encode(['status'=>'success']);
    exit;
}

/* SANITIZE INPUT */
$name    = trim(strip_tags($_POST['name'] ?? ''));
$email   = filter_var($_POST['mail'] ?? '', FILTER_VALIDATE_EMAIL);
$phone   = trim(strip_tags($_POST['phone'] ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));

if (!$name || !$email || strlen($message) < 5) {
    echo json_encode(['status'=>'error','message'=>'Invalid form data']);
    exit;
}

/* EMAIL BODY */
$htmlMessage = "
<p><strong>Name:</strong> $name</p>
<p><strong>Email:</strong> $email</p>
<p><strong>Phone:</strong> $phone</p>
<p><strong>Message:</strong><br>$message</p>";

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.sendgrid.net';
    $mail->SMTPAuth = true;
    $mail->Username = 'apikey';
    $mail->Password = 'j29v3VbxQh6s50xH_9kGtw';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('noreply@manifoods.com', 'Mani Foods');
    $mail->addAddress('customerservice@manifoods.com');

    $mail->isHTML(true);
    $mail->Subject = 'New Contact Form Submission - Mani Foods';
    $mail->Body = $htmlMessage;
    $mail->send();

    echo json_encode(['status'=>'success']);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>'Mail sending failed']);
}
