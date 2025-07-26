<?php
include "../conn.php";
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: ./?page=login");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subscriber_airtime = $_POST['subscriber_airtime'];
    $subscriber_data = $_POST['subscriber_data'];
    $subscriber_electricity = $_POST['subscriber_electricity'];
    $subscriber_tv = $_POST['subscriber_tv'];
    $subscriber_smile = $_POST['subscriber_smile'];

    $agent_airtime = $_POST['agent_airtime'];
    $agent_data = $_POST['agent_data'];
    $agent_electricity = $_POST['agent_electricity'];
    $agent_tv = $_POST['agent_tv'];
    $agent_smile = $_POST['agent_smile'];

    $vendor_airtime = $_POST['vendor_airtime'];
    $vendor_data = $_POST['vendor_data'];
    $vendor_electricity = $_POST['vendor_electricity'];
    $vendor_tv = $_POST['vendor_tv'];
    $vendor_smile = $_POST['vendor_smile'];

    // Insert settings into the database
    $query = "INSERT INTO settings (category, airtime, data, electricity, tv_subscription, smile) VALUES 
              ('subscriber', ?, ?, ?, ?, ?),
              ('agent', ?, ?, ?, ?, ?),
              ('vendor', ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("dddddddddddddd",
        $subscriber_airtime, $subscriber_data, $subscriber_electricity, $subscriber_tv, $subscriber_smile,
        $agent_airtime, $agent_data, $agent_electricity, $agent_tv, $agent_smile,
        $vendor_airtime, $vendor_data, $vendor_electricity, $vendor_tv, $vendor_smile
    );
    $stmt->execute();
    $stmt->close();
}
?>