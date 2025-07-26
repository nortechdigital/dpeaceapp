<?php
// Start session
session_start();

// Destroy the session to log the user out
session_unset();
session_destroy();

// Redirect to login page
header("Location: ../?page=login");
exit;
?>