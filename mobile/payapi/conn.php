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