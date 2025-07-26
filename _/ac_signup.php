<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
session_start();
include "../conn.php";

// Query to count registered users
$sql = "SELECT COUNT(*) AS user_count FROM users";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $userCount = $row['user_count'];
  if ($userCount >= 2500000) {
  	$_SESSION['error'] = 'Server busy. Please try again later.';
	die(header('location: ../?page=signup'));
  }
}
// $conn->close();

if (!isset($_POST['captcha']) || $_POST['captcha'] !== $_SESSION['captcha']) {
	$_SESSION['error'] = 'CAPTCHA verification failed. Please try again.';
	die(header('location: ../?page=signup'));
}
// After successful verification, regenerate CAPTCHA
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789qwertyuiopasdfghjklzxcvbnm';
$_SESSION['captcha'] = substr(str_shuffle($chars), 0, 6);

$sql = "SELECT email FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $emailList[] = $row['email'];
  }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Adjust path to where you installed PHPMailer
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Helper function to sanitize input

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = isset($_POST['firstname']) ? test_input($_POST['firstname']) : '';
	$lastname = isset($_POST['lastname']) ? test_input($_POST['lastname']) : '';
	$email = isset($_POST['email']) ? test_input($_POST['email']) : '';
	$phone = isset($_POST['phone']) ? test_input($_POST['phone']) : '';
	$password = isset($_POST['password']) ? test_input($_POST['password']) : '';
    $password = password_hash($password, PASSWORD_DEFAULT);
    $otp = rand(100000, 999999);
    $status = 0;

	if (in_array($email, $emailList)) {
    	$_SESSION['error'] = 'Email already exists! Please use a different email or Login to continue.';
		die(header('location: ../?page=signup'));
    }

    // Store user data in session
    $_SESSION['pre_user'] = [
        'firstname' => $firstname,
        'lastname' => $lastname,
        'email' => $email,
        'phone' => $phone,
        'otp' => $otp,
        'status' => $status,
        'password' => $password
    ];


    if (!empty($email)) {
        try {

            $mail = new PHPMailer();
            $mail->Encoding = "base64";
            $mail->SMTPAuth = true;
            $mail->Host = "smtp.zeptomail.com";
            $mail->Port = 587;
            $mail->Username = "emailapikey";
            $mail->Password = 'wSsVR61/8xaiC64pmGCrc7o+mgtWUgygQ0h5jVKk6ieuHq+Xosduk0OaAlDxSPQZQzM6QGETrOounRpT22YJiox5zVEHXiiF9mqRe1U4J3x17qnvhDzMWm5YlhaIJYsKwARonWVkEc8n+g==';
            $mail->SMTPSecure = 'TLS';
            $mail->isSMTP();
            $mail->IsHTML(true);
            $mail->CharSet = "UTF-8";
            $mail->From = "noreply@dpeaceapp.com";
            $mail->addAddress($email);
            $mail->Body='
                <html>
                <head>
                    <title>Email Verification</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .header { color: #3366ff; text-align: center; }
                        .content { margin: 20px 0; }
                        .footer { font-size: 12px; color: #666; text-align: center; }
                        .logo { display: block; margin: 0 auto; width: 150px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <img src="https://dpeaceapp.com/img/dpeace-app.png" alt="DPeace Logo" class="logo">
                        <h1>OTP</h1>
                    </div>
                    <div class="content">
                        <p>Hello ' . htmlspecialchars($firstname) . ' ' . htmlspecialchars($lastname) . ',</p>
                        <p>Use <strong>' . $otp . '</strong> to verify your account.</p>
                        <p>If you did not request this, please ignore this email.</p>
                        <p>Thank you for using DPeaceApp!</p>
                        <p>For any assistance, feel free to contact us.</p>
                        <p>Email: support@dpeaceapp.com</p>
                    </div>
                    <div class="footer">
                        <p>© ' . date('Y') . ' DPeaceApp. All rights reserved.</p>
                    </div>
                </body>
                </html>
            ';
            $mail->Subject="DPeace App - Email Verification";
            // $mail->SMTPDebug = 1;
            // $mail->Debugoutput = function($str, $level) {echo "debug level $level; message: $str"; echo "<br>";};
            if(!$mail->Send()) {
                echo $_SESSION['error'] = "Mail sending failed";
            } else {
                die(header("Location: ../?page=verify_otp"));
            }
        } catch (Exception $e) {
            echo $_SESSION['error'] = "Mailer Error: " . $mail->ErrorInfo;
        }
    }
}

header('location: ' . $_SERVER['HTTP_REFERER']);
