<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$echo = "";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Adjust path to where you installed PHPMailer
require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? test_input($_POST['email']) : '';
    
    // Debugging output
    echo "Email entered: " . htmlspecialchars($email) . "<br>";

    // Check if user exists
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", "$username", "$password");
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Generate token and expiry
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in database
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        $stmt->execute([$token, $expiry, $user['id']]);
        
        $resetLink = "https://dpeaceapp.com/?page=reset_password&token=$token";

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
                    <title>Password Reset</title>
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
                        <h1>Password Reset</h1>
                    </div>
                    <div class="content">
                        <p>Hello,</p>
                        <p>Click the link below to reset your password:</p>
                        <p><a href="' . $resetLink . '">' . $resetLink . '</a></p>
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
            $mail->Subject="DPeace App - Password Reset";
            // $mail->SMTPDebug = 1;
            // $mail->Debugoutput = function($str, $level) {echo "debug level $level; message: $str"; echo "<br>";};
            if(!$mail->Send()) {
                echo $_SESSION['error'] = "Mail sending failed";
            } else {
                $echo = "If an account exists with that email, a reset link has been sent.";
            }
        } catch (Exception $e) {
            $echo = "Mailer Error: " . $mail->ErrorInfo;
        }
    } else {
        $echo = "If an account exists with that email, a reset link has been sent.";
    }
}
?>
<section class="vh-100 d-flex align-items-center">
  <div class="container py-5">
    <div class="row justify-content-center">
      <!-- Left Side Image -->
      <div class="col-lg-6 d-none d-lg-block text-center">
        <img src="./img/dpeace-app.png" alt="Site Logo" class="img-fluid" style="max-width: 100%; height: auto;">
      </div>

      <!-- Login Form -->
      <div class="col-lg-5">
		<?php if($echo): ?>
		<div class="alert alert-warning alert-dismissible fade show" role="alert">
  			<strong></strong> <?= $echo ?>
  			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
        <?php endif; ?>
        <form action="" method="post" class="card shadow-lg">
          <div class="card-body p-4">
            <div class="mb-3 text-center">
              <h1 class="h3 fw-bold"><u>Forgot Password</u></h1>
            </div>

            <!-- Email Field -->
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" name="email" id="email" class="form-control" required>
            </div>

           
            <!-- Send otp Button -->
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Reset Password</button>
            </div>
          </div>
        </form>

        <!-- Login Page Links -->
        <div class="text-center mt-3">
          <a href="?page=login" class="btn btn-outline-secondary">Back to Login Page</a>
        </div>
      </div>
    </div>
  </div>
</section>