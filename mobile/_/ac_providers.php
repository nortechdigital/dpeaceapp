<?php
// filepath: /C:/xampp/htdocs/dpeace/admin/pg/providers.php
include "../conn.php";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $logo = $_POST['logo'];
    $description = $_POST['description'];

    // Insert provider details into the database
    $query = "INSERT INTO providers (name, logo, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $name, $logo, $description);
    if ($stmt->execute()) {
        $success_message = "Provider details submitted successfully.";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>