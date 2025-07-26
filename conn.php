<?php
// Database connection
// Determine connection variables based on URL
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
  $host = 'localhost';
  $username = 'root';
  $password = '';
  $dbname = 'dpeaceapp';
} else {
  $host = 'localhost';
  $username = 'dpeaceapp';
  $password = 'nx6Y0ZnqG5uwOXC';
  $dbname = 'dpeaceapp';
}
// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function generate_csrf_token() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($submitted_token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submitted_token)) {
        // Token is invalid, handle the error
        // die("CSRF token validation failed");
    	$_SESSION['error'] = 'ACCESS DENIED';
    	die(header('location: ' . $_SERVER['HTTP_REFERER']));
    }
    // Optionally regenerate the token after successful validation
    unset($_SESSION['csrf_token']);
}