<?php

// Function to get user details
function getUserDetails($apiKey) {
    $url = "https://arewaglobal.co/api/user/";
    

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Token " . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POST, true);

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        echo "Error: " . curl_error($ch);
    }

    // Close the cURL session
    curl_close($ch);

    // Decode and return the response
    return json_decode($response, true);
}

// Example usage
$apiKey = "6CE1CB3cdI7AfswCC1xHzd2pcAA7F265kBA2A4xqAC9C3l3A0tCBbogr84xi";
$userDetails = getUserDetails($apiKey);
echo "<pre>" . print_r($userDetails, true) . "</pre>";


// Function to buy data
function buyData($apiKey, $phone, $plan, $network, $portedNumber) {
    $url = "https://arewaglobal.co/api/data/";

    // Data to send in the request body
    $data = [
        "phone" => $phone,
        "plan" => $plan,
        "network" => $network,
        "ported_number" => $portedNumber
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
    }

    // Close the cURL session
    curl_close($ch);

    // Decode and return the response
    return json_decode($response, true);
}

// Example usage
// $apiKey = "3CCyAAD9nAx1C5A2C88BcqBezv1gobCHxw7pA6ACsm3i2r60t4Chd3BdAGB4";
// $phone = "09044572815";
// $plan = "185";   // Plan ID
// $network = "2"; // MTN = 1
// $portedNumber = "false"; // or "false"

// $dataResponse = buyData($apiKey, $phone, $plan, $network, $portedNumber);
// echo "<pre>" . print_r($dataResponse, true) . "</pre>";



// Function to buy airtime
function buyAirtime($apiKey, $phone, $amount, $network, $portedNumber) {
    $url = "https://arewaglobal.co/api/airtime/";

    // Data to send in the request body
    $data = [
        "phone" => $phone,
        "amount" => $amount,
        "network" => $network,
        "ported_number" => $portedNumber
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
    }

    // Close the cURL session
    curl_close($ch);

    // Decode and return the response
    return json_decode($response, true);
}

// Example usage
$apiKey = "6CE1CB3cdI7AfswCC1xHzd2pcAA7F265kBA2A4xqAC9C3l3A0tCBbogr84xi";
$phone = "08065615684";
$amount = "50"; // Amount to buy
$network = "1"; // MTN = 1
$portedNumber = "false"; // or "false"

$airtimeResponse = buyAirtime($apiKey, $phone, $amount, $network, $portedNumber);
echo "<pre>" . print_r($airtimeResponse, true) . "</pre>";


?>
