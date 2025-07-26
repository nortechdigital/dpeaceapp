<?php
session_start();
include "../conn.php";
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $otp = rand(100000, 999999);
        $hashed_otp = password_hash($otp, PASSWORD_DEFAULT);
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_otp'] = $hashed_otp;
        $_SESSION['otp_expiry'] = time() + (5 * 60); // 5 minutes expiry

        // Send OTP via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USERNAME'];
            $mail->Password = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['SMTP_PORT'];

            $mail->setFrom($_ENV['SMTP_USERNAME'], 'Your Website');
            $mail->addAddress($email);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body = "Your OTP is: $otp. Expires in 5 minutes.";

            $mail->send();
            header("Location: verify_otp.php");
            exit;
        } catch (Exception $e) {
            echo "<p style='color:red;'>Email sending failed.</p>";
        }
    } else {
        echo "<p style='color:red;'>No account found with that email.</p>";
    }
}
?>

