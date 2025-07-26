<?php

// ARTX API Configuration
$apiEndpoint = 'https://artxh1.sochitel.com/api.php';
$username = 'dpeaceapp.api.ngn'; 
$password = 'login@DPeaceAdmin1234'; 

// Generate a unique salt for each request (up to 40 chars)
$salt = bin2hex(random_bytes(20)); // 40-character random string

// Calculate the password hash (SHA1)
$sha1Password = sha1($password); // Step 1: SHA1 of plain password
$passwordHash = sha1($salt . $sha1Password); // Step 2: SHA1(salt + SHA1(password))

// Prepare the request payload
$payload = [
    'auth' => [
        'username' => $username,
        'salt' => $salt,
        'password' => $passwordHash,
        'signature' => '' // Optional (not used here)
    ],
    'version' => 5,
    'command' => 'getBalance'
];

// Convert payload to JSON
$jsonPayload = json_encode($payload);

// Initialize cURL request
$ch = curl_init($apiEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonPayload)
]);

// Execute the request
$response = curl_exec($ch);
// print_r($response);die;

// Check for errors
if (curl_errno($ch)) {
    die('cURL Error: ' . curl_error($ch));
}

// Close cURL session
curl_close($ch);

// Decode the JSON response
$result = json_decode($response, true);

$bal_nomiworld = $result['result']['value'];

// Check if the response is valid
if (json_last_error() !== JSON_ERROR_NONE) {
    die('Invalid JSON response');
}

// Output the result
if (isset($result['status']['type']) && $result['status']['type'] === 0) {
    // Success
    $result['result']['value'];
} else {
    // Error
    echo "Error: " . $result['status']['name'] . " (Code: " . $result['status']['id'] . ")";
}

?>