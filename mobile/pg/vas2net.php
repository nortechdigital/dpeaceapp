<?php

// Define API endpoint
$baseUrl = "https://b2bsandbox.v2nportal.com/"; // Replace with the actual base URL
$envUri = "v2/";   // Replace with the environment URI
$metaSubUri = "meta/"; // Replace with the meta sub URI

$url = "{$baseUrl}{$envUri}{$metaSubUri}getDetails";

// Define username and password
$username = "dpeaceapp@info"; // Replace with actual username
$password = "D$34eace$@App"; // Replace with actual password

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

// Use Basic Authentication
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

// Follow redirects
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Close cURL session
curl_close($ch);

// Check if the response is already JSON
$jsonData = json_decode($response, true);

if ($jsonData !== null) {
    // It's already JSON, print it
    header('Content-Type: application/json');
    echo json_encode($jsonData, JSON_PRETTY_PRINT);
} else {
    // Convert HTML to JSON (basic extraction)
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Suppress HTML parsing errors
    $dom->loadHTML($response);
    libxml_clear_errors();
    
    // Extract text from body
    $bodyText = $dom->textContent;

    // Create a JSON response
    $htmlAsJson = [
        "status" => $httpCode,
        "message" => "HTML Response converted to JSON",
        "content" => trim($bodyText)
    ];

    header('Content-Type: application/json');
    echo json_encode($htmlAsJson, JSON_PRETTY_PRINT);
}

?>