<?php
session_start();
include "../conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['otp_email'];
    $otp = trim($_POST['otp']);

    if (empty($otp)) {
        echo "<p style='color: red;'>OTP is required.</p>";
        exit;
    }

    // Fetch OTP from database
    $stmt = $conn->prepare("SELECT otp_code, otp_expiry FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($stored_otp_hash, $otp_expiry);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        if (password_verify($otp, $stored_otp_hash) && strtotime($otp_expiry) > time()) {
            $_SESSION['user_logged_in'] = true;

            // Remove OTP after successful login
            $clearStmt = $conn->prepare("UPDATE users SET otp_code = NULL, otp_expiry = NULL WHERE email = ?");
            $clearStmt->bind_param("s", $email);
            $clearStmt->execute();
            $clearStmt->close();

            echo "<script>alert('OTP verified successfully. Redirecting to dashboard...'); window.location.href = '../?page=dashboard';</script>";
            exit;
        } else {
            echo "<p style='color: red;'>Invalid or expired OTP.</p>";
            exit;
        }
    } else {
        echo "<p style='color: red;'>No OTP found. Request a new one.</p>";
        exit;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
</head>
<body>
    <h2>Enter OTP</h2>
    <form method="post">
        <input type="text" name="otp" placeholder="Enter OTP" required>
        <button type="submit">Verify OTP</button>
    </form>
</body>
</html>
