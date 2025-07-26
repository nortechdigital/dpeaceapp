<?php
session_start();
include "../conn.php";
include "../_/ac_config.php";

// Check if user session is set
if (!isset($_SESSION['user_id'])) {
    echo "User session not found.";
    exit;
}
print_r($_POST);die;

$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];
$phone_number = $phone = $_SESSION['phone'];
$fullname = $firstname . " " . $lastname;

$query = "SELECT * FROM data_plans WHERE provider = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $amount);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Get price for the selected data plan
$planId = $_POST['data_plan'];
$query = "SELECT price FROM data_plans WHERE id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $planId);
$stmt->execute();
$stmt->bind_result($amount);
$stmt->fetch();
$stmt->close();

// Fetch user balance from the database
$userId = $_SESSION['user_id']; 
$query = "SELECT balance FROM wallets WHERE user_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($balance);
$stmt->fetch();
$stmt->close();

// Fetch input values
$phone = $_POST['phone_number'] ?? '';
$plan = $_POST['data_plan'] ?? '';
$network = $_POST['network'] ?? '';
$portedNumber = $_POST['ported_number'] ?? '';
$provider = $_POST['provider'] ?? '';

// Validate input values
if (empty($phone) || empty($plan) || empty($network) || empty($portedNumber)) {
    echo "<script>
        alert('All fields are required.');
        window.location.href = '../?page=data';
    </script>";
    exit;
}

if (!is_numeric($plan) || $plan <= 0) {
    echo "<script>
        alert('Invalid plan.');
        window.location.href = '../?page=data';
    </script>";
    exit;
}


if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
    echo "<script>
        alert('Invalid phone number.');
        window.location.href = '../?page=data';
    </script>";
    exit;
}

$apiKey = AREWA_API_KEY;

// Function to verify phone number with the network provider
function verifyPhoneNumber($apiKey, $phone, $network) {
    $url = AREWA_BASE_URL . '/api/verify_number/';

    // Data to send in the request body
    $data = [
        "phone" => $phone,
        "network" => $network
    ];

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Token " . $apiKey,
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        echo "Error: " . curl_error($ch);
        return false;
    }

    // Close the cURL session
    curl_close($ch);

    // Decode and return the response
    return json_decode($response, true);
}

// Function to purchase data
function buyData($apiKey, $phone, $plan, $network, $portedNumber) {
    $url = AREWA_BASE_URL . '/api/data/';

    // Data to send in the request body
    $data = [
        "phone" => $phone,
        "plan" => $plan,
        "network" => $network,
        "ported_number" => $portedNumber
    ];


    "<pre>" . print_r($data, true) . "</pre>";

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Token " . $apiKey,
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        echo "<script>
            alert('Error: " . curl_error($ch) . "');
            window.location.href = '../?page=data';
        </script>";
        return false;
    }

    // Close the cURL session
    curl_close($ch);

    // Decode and return the response
    return json_decode($response, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify phone number with the network provider
    $apiKey = AREWA_API_KEY;
    $verifyResponse = verifyPhoneNumber($apiKey, $phone, $network, $plan, $portedNumber);

    // Calculate the discounted amount
    $discount = (int) $amount * 0.02;
    $amount = $amount - $discount;
    echo $discount;
    echo $amount;

    // Check if the user has enough balance to purchase the airtime
    if ($balance >= $amount) {
        // Proceed to purchase the airtime
        $portedNumber = "false";
        $phone = $phone;
        $amount = $amount;
        $network = $network;
        $response = buyData($apiKey, $phone, $plan, $network, $portedNumber);
        
        // Check for errors in the response
        if ($response && $response['status'] == 'fail') {
            echo "<script>
                alert('Error purchasing data. Please try again later.');
                window.location.href = '../?page=data';
            </script>";
             // Log the transaction
             $logQuery = "INSERT INTO transactions (user_id, fullname, phone_number, amount, product_description, status) VALUES (?, ?, ?, ?, ?, ?)";
             $logStmt = $conn->prepare($logQuery);
             if (!$logStmt) {
                 die("Error preparing statement: " . $conn->error);
             }
             $status = "failed";
             $details = "Purchased $provider data for " . $phone ;
             $logStmt->bind_param("issdss", $userId, $fullname, $phone, $amount, $details, $status);
             $logStmt->execute();
             $logStmt->close();
        } 


        // Check for purchase response success
        if (isset($response['status']) && $response['status'] == 'success') {
            // Update the user's balance in the database
            $newBalance = $balance - $amount;
            $updateQuery = "UPDATE wallets SET balance = ? WHERE user_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            if (!$updateStmt) {
                die("Error preparing statement: " . $conn->error);
            }
            $updateStmt->bind_param("di", $newBalance, $userId);
            $updateStmt->execute();
            $updateStmt->close();

            "<pre>" . print_r($response, true) . "</pre>";
            echo "<script>
                alert('$provider data purchase successful!');
                window.location.href = '../?page=data';
            </script>";
            // Log the transaction
            $logQuery = "INSERT INTO transactions (user_id, amount, product_description, status) VALUES (?, ?, ?, ?)";
            $logStmt = $conn->prepare($logQuery);
            if (!$logStmt) {
                die("Error preparing statement: " . $conn->error);
            }
            $status = "failed";
            $details = "Purchased $provider data for " . $phone ;
            $logStmt->bind_param("isds", $userId, $amount, $details, $status);
            $logStmt->execute();
            $logStmt->close();
        
        }
    } else {
        echo "<script>
            alert('Insufficient balance to purchase the $provider data.');
            window.location.href = '../?page=data';
        </script>";
        // Log the transaction
        $logQuery = "INSERT INTO transactions (user_id, amount, product_description, status) VALUES (?, ?, ?, ?)";
        $logStmt = $conn->prepare($logQuery);
        if (!$logStmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $status = "failed";
        $details = "Purchased $provider data for " . $phone ;
        $logStmt->bind_param("idss", $userId, $amount, $details, $status);
        $logStmt->execute();
        $logStmt->close();
    }
}
?>