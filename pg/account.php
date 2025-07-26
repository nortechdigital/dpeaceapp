<?php

// Monnify API Credentials
$apiKey = 'MK_TEST_NSAU2CN12T';
$apiSecret = 'UGZ0RRHR69CWXE9D9KBUJWY8LSBNVRJ6';

// Step 1: Get Access Token
$tokenUrl = "https://sandbox.monnify.com/api/v1/auth/login"; // Endpoint for fetching token

// Prepare the Base64-encoded Authorization header (API key + API secret)
$credentials = base64_encode("$apiKey:$apiSecret");

// Prepare the headers for the request
$headers = [
    "Authorization: Basic $credentials",
    "Content-Type: application/json"
];

// Initialize cURL for the token request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);

// Execute the token request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'Error fetching token: ' . curl_error($ch);
    exit;
}

// Decode the response to get the access token
$responseData = json_decode($response, true);

// Check if we successfully got the access token
if (isset($responseData['accessToken'])) {
    $accessToken = $responseData['accessToken'];
    echo "Access Token Retrieved Successfully: " . $accessToken . "<br>";

    // Step 2: Use the Access Token to Get Reserved Account Details
    $accountId = 'IxSN1ZsPOS';  // Example reserved account ID
    $endpoint = "https://sandbox.monnify.com/api/v2/bank-transfer/reserved-accounts/{$accountId}";

    // Setup the headers with the access token
    $headers = [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ];

    // Initialize cURL for the reserved account request
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Execute the reserved account details request
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'Error retrieving reserved account details: ' . curl_error($ch);
    } else {
        // Decode and handle the response from Monnify API
        $responseData = json_decode($response, true);

        // Check for success and display the reserved account details
        if (isset($responseData['status']) && $responseData['status'] == 'SUCCESS') {
            echo "Reserved Account Details Retrieved Successfully:<br>";
            echo "<pre>" . print_r($responseData, true) . "</pre>";
        } else {
            // Output error if the request failed
            $errorMessage = isset($responseData['message']) ? $responseData['message'] : 'Unknown error';
            echo "Failed to Retrieve Account Details: " . htmlspecialchars($errorMessage);
        }
    }
} else {
    // If we did not get the token, display the error message
    echo "Error fetching access token: " . print_r($responseData, true);
}

// Close the cURL session
curl_close($ch);

?>
