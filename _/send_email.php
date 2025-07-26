<?php
require 'conn.php';
require '../vendor/autoload.php'; // Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendLoginNotification($to, $firstname, $lastname) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@example.com'; // Replace with your SMTP username
        $mail->Password = 'your-email-password'; // Replace with your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('no-reply@dpeaceapp.com', 'DPeace App');
        $mail->addAddress($to, "$firstname $lastname");

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Login Notification';
        $mail->Body = "Hello $firstname $lastname,<br><br>You have successfully logged into your account on " . date("Y-m-d H:i:s") . ".<br><br>If this wasn't you, please contact support immediately.";
        $mail->AltBody = "Hello $firstname $lastname,\n\nYou have successfully logged into your account on " . date("Y-m-d H:i:s") . ".\n\nIf this wasn't you, please contact support immediately.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

$to = isset($_POST['to']) ? test_input($_POST['to']) : '$email';  
$subject = isset($_POST['subject']) ? test_input($_POST['subject']) : '';
$name = isset($_POST['name']) ? test_input($_POST['name']) : 'Anonymous';
$email = isset($_POST['email']) ? test_input($_POST['email']) : '';
// $address = isset($_POST['address']) ? test_input($_POST['address']) : '';
$phone = isset($_POST['phone']) ? test_input($_POST['phone']) : '';
$message = isset($_POST['message']) ? test_input($_POST['message']) : '';
$message = "$message \nPhone: $phone \nEmail: $email";
$message = wordwrap($message, 70);
$headers = "From: no-reply@dpeaceapp.com" . "\r\n";

if (sendLoginNotification($to, $firstname, $lastname)) {
  $msg = ' request sent successfully!';
} else {
  $msg = ' request not sent!';
}

if ($return == true) die(header('location: ' . $_SERVER['HTTP_REFERER'] . '&msg=' . $msg));
header('location: ../?page=' . $pg . '&msg=' . $msg);
?>