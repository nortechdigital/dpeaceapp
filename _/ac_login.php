<?php
// Enable error reporting at the very top
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
include "../conn.php";

// Initialize session variables for tracking failed login attempts
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
}
if (!isset($_SESSION['last_failed_attempt'])) {
    $_SESSION['last_failed_attempt'] = 0;
}

// Check if POST request is made
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST['login']); // This can be either email or username
    $password = trim($_POST['password']); // Password entered by user

    // Check if login is disabled due to too many failed attempts
    if ($_SESSION['failed_attempts'] >= 3 && (time() - $_SESSION['last_failed_attempt']) < 60) {
        echo "<script>alert('Too many failed login attempts. Please try again after 60 seconds.');</script>";
        echo "<script>window.location.href = '../?page=login';</script>";
        exit;
    }

    // Prepare the SQL query to check for email or username
    $stmt = $conn->prepare("SELECT id, firstname, lastname, password, email, role, phone, category FROM users WHERE email = ? OR username = ?");
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters to the query
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Bind result columns
        $stmt->bind_result($user_id, $firstname, $lastname, $stored_password, $email, $role, $phone, $category);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $stored_password)) {
            // Password is correct, reset failed attempts
            $_SESSION['failed_attempts'] = 0;
            $_SESSION['last_failed_attempt'] = 0;

            // Set session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;
            $_SESSION['firstname'] = $firstname;
            $_SESSION['lastname'] = $lastname;
            $_SESSION['role'] = $role;
            $_SESSION['phone'] = $phone;
            $_SESSION['category'] = $category;

            // Send email notification
            if (!empty($email)) {
                $to = $email;
                $subject = 'DPeace App - Successful Login Notification';
                $message = '
                <html>
                <head>
                    <title>Successful Login Notification</title>
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
                        <h1>Login Notification</h1>
                    </div>
                    <div class="content">
                        <p>Hello ' . $firstname . ' ' . $lastname . ',</p>
                        <p>You have successfully logged into your account on ' . date('Y-m-d H:i:s') . '.</p>
                        <p>If this was not you, please contact our support team immediately.</p>
                        <p>Email: support@dpeaceapp.com</p>
                    </div>
                    <div class="footer">
                        <p>© ' . date('Y') . ' DPeaceApp. All rights reserved.</p>
                    </div>
                </body>
                </html>';

                $headers = implode("\r\n", [
                    'From: no-reply@dpeaceapp.com',
                    'Reply-To: no-reply@dpeaceapp.com',
                    'MIME-Version: 1.0',
                    'Content-type: text/html; charset=UTF-8',
                    'X-Mailer: PHP/' . phpversion()
                ]);

                mail($to, $subject, $message, $headers);
            }

            // Redirect based on the role
            if ($role == 'admin') {
                $_SESSION['admin'] = true;
                header("Location: ../admin/?page=dashboard");
            } elseif ($role == 'customer_care') {
                $_SESSION['admin'] = true;
                header("Location: ../admin/?page=dashboard");
            }else {
                header("Location: ../?page=dashboard");
            }
            exit;
        } else {
            // Password is incorrect, increment failed attempts
            $_SESSION['failed_attempts']++;
            $_SESSION['last_failed_attempt'] = time();
            echo "<script>alert('Incorrect Username or Password.');</script>";
            echo "<script>window.location.href = '../?page=login';</script>";
        }
    } else {
        echo "<script>alert('User not found.');</script>";
        echo "<script>window.location.href = '../?page=login';</script>";
    }

    // Close the prepared statement
    $stmt->close();
}

// Close database connection
$conn->close();
?>