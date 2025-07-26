<?php
session_start();
require '../conn.php'; // Ensure this file contains the database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

// Sanitize input function
function sanitizeInput($conn, $input) {
    return htmlspecialchars(trim(mysqli_real_escape_string($conn, $input)), ENT_QUOTES, 'UTF-8');
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch and sanitize user input
$firstname = sanitizeInput($conn, $_POST['firstname']);
$lastname  = sanitizeInput($conn, $_POST['lastname']);
$email     = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$phone     = sanitizeInput($conn, $_POST['phone']);
$user_id   = $_SESSION['user_id']; // Assuming user_id is stored in session

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email format.");
}

// Validate phone number (optional)
if (!preg_match("/^\+?[0-9]{10,15}$/", $phone)) {
    die("Invalid phone number format.");
}

// Prepare and execute the update query
$sql = "UPDATE users SET firstname = ?, lastname = ?, email = ?, phone = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $firstname, $lastname, $email, $phone, $user_id);

if ($stmt->execute()) {
    // Update session variables
    $_SESSION['firstname'] = $firstname;
    $_SESSION['lastname']  = $lastname;
    $_SESSION['email']     = $email;
    $_SESSION['phone']     = $phone;

    // Redirect with success message
    header("Location: ../?page=profile&success=Profile updated successfully");
    exit();
} else {
    die("Error updating profile: " . $stmt->error);
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
