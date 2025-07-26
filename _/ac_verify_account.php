<?php
session_start();
include "../conn.php";
// Get user details from the users table
$userId = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "User not found.";
    exit;
}

$user = $result->fetch_assoc();
$firstName = $user['firstname'];
$lastName = $user['lastname'];
$phone = $user['phone'];
$email = $user['email'];
$un = $user['username'];

$stmt->close();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the BVN and date of birth from the form
    $bvn = $_POST['bvn'];
    $dateofbirth = $_POST['dateofbirth'];

        // Monnify API credentials
    $monnify_base_url = "https://api.monnify.com";
    $monnify_api_key = "MK_PROD_SX11TPSB53";
    $monnify_secret_key = "VF62JU0QJJ37ECT4N1TJUEC4DPP09YJN";

    // Generate auth token for Monnify API
    $authToken = base64_encode("$monnify_api_key:$monnify_secret_key");

    // Function to make a cURL request
    function makeCurlRequest($url, $headers, $postFields = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($postFields) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }
        $response = curl_exec($ch);
        if ($response === false) {
            echo "Curl error: " . curl_error($ch);
            curl_close($ch);
            exit;
        }
        curl_close($ch);
        return json_decode($response, true);
    }

    // Step 1: Get Access Token from Monnify
    $authHeaders = [
        "Authorization: Basic $authToken",
        "Content-Type: application/json"
    ];

    $authResponse = makeCurlRequest("$monnify_base_url/api/v1/auth/login", $authHeaders, json_encode([])); // Set request method to POST
    
    // Log raw response for debugging
    // echo "<pre>Auth Response: " . htmlspecialchars(json_encode($authResponse)) . "</pre>";

    if (!isset($authResponse['responseBody']['accessToken'])) {
        echo "<pre>Failed to get access token from Monnify. Response: " . htmlspecialchars(json_encode($authResponse)) . "</pre>";
        exit;
    }
    $accessToken = $authResponse['responseBody']['accessToken'];


// Set your base URL and Bearer token
$base_url = "$monnify_base_url/api/v2/bank-transfer/reserved-accounts";  // Replace with actual base URL
$token = $accessToken;  // Replace with your actual Bearer token

// Prepare the data to send in the request body
// Generate a 10 character alphanumeric account reference
function generateAccountReference($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

$accountReference = generateAccountReference();
$accountNumber = generateAccountReference(15);

$data = [
    'accountReference' => $accountReference,
    'accountName' => "$firstName",
    'currencyCode' => 'NGN',
    'contractCode' => '825143057598',
    'customerEmail' => $email,
    'customerName' => "$firstName $lastName",
    'bvn' => $bvn,
    'getAllAvailableBanks' => true
];

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $base_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Execute the cURL request
$response = curl_exec($ch);

// Check for errors
if ($response === false) {
    echo 'Curl error: ' . curl_error($ch);
} else {
    
    $responseData = json_decode($response, true);
    if (isset($responseData['responseBody'])) {
        $accountNumber = $responseData['responseBody']['accounts'][0]['accountNumber'];
        $accountName = $responseData['responseBody']['accounts'][0]['accountName'];
        $bankCode = $responseData['responseBody']['accounts'][0]['bankCode'];
        $bankName = $responseData['responseBody']['accounts'][0]['bankName'];
        $reservationReference = $responseData['responseBody']['reservationReference'];
        $reservedAccountType = $responseData['responseBody']['reservedAccountType'];
        $status = $responseData['responseBody']['status'];
        $createdOn = $responseData['responseBody']['createdOn'];
        $bvn = $responseData['responseBody']['bvn'];
    } else {
        echo "Error: Invalid response from Monnify API.";
        exit;
    }
    // Insert the user's account details into the database
    $query = "INSERT INTO wallet_accounts (user_id, account_number, account_name, bank_code, bank_name, reservation_reference, reserved_account_type, status, created_on, bvn) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        echo "Error preparing statement: " . htmlspecialchars($conn->error);
        exit;
    }

    // Bind parameters
    $stmt->bind_param("isssssssss", $userId, $accountNumber, $accountName, $bankCode, $bankName, $reservationReference, $reservedAccountType, $status, $createdOn, $bvn);

    // Execute the query
    if ($stmt->execute()) {
        // If insertion is successful, update the status in the users table
        $updateQuery = "UPDATE users SET status = 1 WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        if ($updateStmt === false) {
            echo "Error preparing update statement: " . htmlspecialchars($conn->error);
            exit;
        }

        // Bind and execute the update statement
        $updateStmt->bind_param("i", $userId);
        if ($updateStmt->execute()) {
            // Success: Show modal alert and redirect
            echo "<script type='text/javascript'>
                    alert('Account verified');
                    window.location.href = '../?page=wallet';
                </script>";
        } else {
            echo "Error updating user status: " . htmlspecialchars($conn->error);
        }

        // Close the update statement
        $updateStmt->close();
    } else {
        echo "Error inserting wallet account: " . htmlspecialchars($conn->error);
    }

    // Close the insert statement
    $stmt->close();

    }
}
?>