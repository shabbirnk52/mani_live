<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Google reCAPTCHA Secret Key
$secretKey = "6Leu-iMsAAAAAHB44uSG7Dow8EwiNokMFyQ5mJUa";

// Validate reCAPTCHA
$recaptchaResponse = $_POST['recaptcha_response'];

$verify = file_get_contents(
    "https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaResponse"
);
$captchaSuccess = json_decode($verify, true);

if (!$captchaSuccess["success"] || $captchaSuccess["score"] < 0.5) {
    echo json_encode(["status" => "error", "message" => "reCAPTCHA failed. Please try again."]);
    exit;
}

// Get form fields
$name = $_POST["name"];
$email = $_POST["mail"];
$phone = $_POST["phone"];
$message = nl2br($_POST["message"]);

// Build email template
$htmlMessage = "
<div style='font-family: Arial; padding:20px; border:1px solid #eee;'>
  <h2 style='color:#333;'>New Contact Form Message</h2>
  <p><strong>Name:</strong> $name</p>
  <p><strong>Email:</strong> $email</p>
  <p><strong>Phone:</strong> $phone</p>
  <p><strong>Message:</strong></p>
  <p>$message</p>
</div>";

// Send email to customer service
$mail = new PHPMailer(true);

try {
    // SMTP Settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.sendgrid.net';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'apikey';
    $mail->Password   = 'j29v3VbxQh6s50xH_9kGtw';
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;

    // FROM address
    $mail->setFrom('noreply@manifoods.com', 'Mani Foods Website');

    // TO address (your receiving email)
    $mail->addAddress('customerservice@manifoods.com');

    // Email content
    $mail->isHTML(true);
    $mail->Subject = "New Contact Submission - Mani Foods";
    $mail->Body    = $htmlMessage;

    $mail->send();

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Email Error: {$mail->ErrorInfo}"]);
    exit;
}

// AUTO-REPLY to customer
try {
    $reply = new PHPMailer(true);

    $reply->isSMTP();
    $reply->Host       = 'smtp.sendgrid.net';
    $reply->SMTPAuth   = true;
    $reply->Username   = 'apikey';
    $reply->Password   = 'j29v3VbxQh6s50xH_9kGtw';
    $reply->SMTPSecure = 'ssl';
    $reply->Port       = 465;

    // From
    $reply->setFrom('noreply@manifoods.com', 'Mani Foods');

    // To user
    $reply->addAddress($email);

    $reply->isHTML(true);
    $reply->Subject = "Thank you for contacting Mani Foods";

    $reply->Body = "
    <div style='font-family: Arial; padding:20px;'>
      <h3>Thank you, $name</h3>
      <p>We have received your message and our team will contact you soon.</p>
      <br>
      <p>Regards,<br>Mani Foods Team</p>
    </div>";

    $reply->send();

} catch (Exception $e) {
    // Silent fail for auto-reply
}

echo json_encode(["status" => "success"]);
?>
