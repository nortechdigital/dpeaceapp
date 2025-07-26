<?php
session_start();
include "../conn.php";

$firstname = $_SESSION['pre_user']['firstname'];
$lastname = $_SESSION['pre_user']['lastname'];
$email = $_SESSION['pre_user']['email'];
$password = $_SESSION['pre_user']['password'];
$phone = $_SESSION['pre_user']['phone'];
$status = $_SESSION['pre_user']['status'];
$category = 'subscriber';
$role = 'user';

// Generate a short username from firstname and lastname
$max_length = 10;
$firstname_part = substr(strtolower($firstname), 0, 5); // Take first 5 characters of firstname
$lastname_part = substr(strtolower($lastname), 0, 4); // Take first 4 characters of lastname
$username = $firstname_part . $lastname_part;

// Ensure the username is not longer than 10 characters
if (strlen($username) > $max_length) {
    $username = substr($username, 0, $max_length);
}

// Check if the generated username already exists
$query = "SELECT COUNT(*) as count FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    // If username exists, append a random number to make it unique
    $username .= rand(100, 999);
    // Ensure the final username is not longer than 10 characters
    $username = substr($username, 0, $max_length);
}

$sql = "INSERT INTO users (firstname, lastname, email, password, phone, status, role, username) VALUES 
('$firstname', '$lastname', '$email', '$password', '$phone', '$status', '$role', '$username')";

if ($conn->query($sql)) {
  $_SESSION['user_id'] = $conn->insert_id;
  $_SESSION['email'] = $email;
  $_SESSION['firstname'] = $firstname;
  $_SESSION['lastname'] = $lastname;
  $_SESSION['phone'] = $phone;
  $_SESSION['role'] = $role; // Store role in session
  $_SESSION['username'] = $username; // Store username in session

  // Create wallet for the user
  $user_id = $_SESSION['user_id'];
  $wallet_balance = 0.00; // Default wallet balance
  $wallet_sql = "INSERT INTO wallets (user_id, balance) VALUES (?, ?)";
  $wallet_stmt = $conn->prepare($wallet_sql);
  $wallet_stmt->bind_param("id", $user_id, $wallet_balance);
  if (!$wallet_stmt->execute()) {
    echo "Error creating wallet: " . $wallet_stmt->error;
    exit;
  }
  echo '<script>alert("Account Created Successfully!")</script>';
  header("Location: ../?page=dashboard");
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

