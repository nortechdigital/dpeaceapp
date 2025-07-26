<?php
session_start();

// Define your VTPass sandbox credentials
$vtpass_api_key = 'sandbox@vtpass.com:sandbox';  // Basic authentication: "email:password"

// The VTPass API endpoint for airtime top-up
$api_url = 'https://sandbox.vtpass.com/api/pay';

// Get user input for airtime purchase (from form submission or set manually)
$provider = isset($_POST['provider']) ? $_POST['provider'] : 'mtn';  // Set provider (e.g., mtn, airtel, glo)
$phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : '';  // User's phone number
$amount = isset($_POST['amount']) ? $_POST['amount'] : '';  // Amount for airtime top-up

// Basic validation
if (empty($phone_number) || empty($amount) || !is_numeric($amount) || $amount <= 0) {
    die('Invalid input! Please provide a valid phone number and amount.');
}

// Prepare data to be sent to the VTPass API
$data = [
    'serviceID' => $provider, // The provider's service ID (e.g., mtn, airtel, glo, etc.)
    'billersCode' => $phone_number,  // User's phone number (billers code for VTPass)
    'amount' => $amount,  // Amount for airtime top-up
    'phone' => $phone_number  // User's phone number (optional for some APIs)
];

// Initialize cURL session
$ch = curl_init($api_url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));  // Send the data as JSON
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . base64_encode($vtpass_api_key),  // Set authorization header
    'Content-Type: application/json'  // Content type as JSON
]);

// Execute the request and get the response
$response = curl_exec($ch);

// Check for cURL errors
if ($response === false) {
    echo 'Curl error: ' . curl_error($ch);
    curl_close($ch);
    exit;
}

// Close cURL session
curl_close($ch);

// Decode the JSON response
$response_data = json_decode($response, true);

// Check if the request was successful
if (isset($response_data['code']) && $response_data['code'] === '000') {
    // Successful response (VTPass returns a success code '000')
    echo 'Airtime purchase successful! Amount: ' . $amount . ' for phone number: ' . $phone_number;
} else {
    // Failed response - print the error message
    echo 'Airtime purchase failed! Error: ' . (isset($response_data['message']) ? $response_data['message'] : 'Unknown error');
}
?>
