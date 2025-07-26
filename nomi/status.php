<?php
// ARTX API Configuration
$apiEndpoint = 'https://artx.sochitel.com/staging.php';
$username = 'testUser1';
$password = 'Test123!';

function analyzeTransactionStatus() {
    global $apiEndpoint, $username, $password;
    
    $userReference = 'AIRTIME_1752853506';
    $requestSalt = '8d7c99ca0a8a6202379d8c9d0f11670900e31203'; // From your raw request
    $requestHash = '5ab87b0b5768d4221bbf7c59f81118ce871835b6'; // From your raw request
    
    // 1. Display the exact request that was sent
    echo "=== ANALYSIS OF TRANSACTION STATUS REQUEST ===" . PHP_EOL;
    echo "API Endpoint: $apiEndpoint" . PHP_EOL;
    echo "HTTP Method: POST" . PHP_EOL;
    echo "Headers:" . PHP_EOL;
    echo "  Content-Type: application/json" . PHP_EOL . PHP_EOL;
    
    echo "Request Body:" . PHP_EOL;
    echo json_encode([
        'auth' => [
            'username' => $username,
            'salt' => $requestSalt,
            'password' => $requestHash,
            'signature' => ''
        ],
        'version' => 5,
        'command' => 'getTransaction',
        'userReference' => $userReference
    ], JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;
    
    // 2. Display and analyze the response
    $rawResponse = <<<RESPONSE
HTTP/2 200 
date: Fri, 18 Jul 2025 15:56:41 GMT
server: Apache
content-type: application/json

{"status":{"id":22,"name":"Invalid Transaction ID","type":2,"typeName":"Failure"},"command":"getTransaction","timestamp":1752854201,"reference":"123456789","result":[]}
RESPONSE;
    
    echo "=== ANALYSIS OF TRANSACTION STATUS RESPONSE ===" . PHP_EOL;
    echo "HTTP Status: 200 OK" . PHP_EOL;
    echo "Response Time: Fri, 18 Jul 2025 15:56:41 GMT" . PHP_EOL;
    echo "Server: Apache" . PHP_EOL . PHP_EOL;
    
    $responseBody = '{"status":{"id":22,"name":"Invalid Transaction ID","type":2,"typeName":"Failure"},"command":"getTransaction","timestamp":1752854201,"reference":"123456789","result":[]}';
    $responseData = json_decode($responseBody, true);
    
    echo "Response Body Analysis:" . PHP_EOL;
    echo "- Status: " . $responseData['status']['name'] . " (ID: " . $responseData['status']['id'] . ")" . PHP_EOL;
    echo "- Type: " . $responseData['status']['typeName'] . PHP_EOL;
    echo "- Command: " . $responseData['command'] . PHP_EOL;
    echo "- Timestamp: " . date('Y-m-d H:i:s', $responseData['timestamp']) . PHP_EOL;
    echo "- Reference: " . $responseData['reference'] . PHP_EOL . PHP_EOL;
    
    // 3. Troubleshooting recommendations
    echo "=== TROUBLESHOOTING RECOMMENDATIONS ===" . PHP_EOL;
    echo "1. Verify the userReference exists in your system" . PHP_EOL;
    echo "2. Check if the transaction was created successfully initially" . PHP_EOL;
    echo "3. Confirm the transaction wasn't purged (older than 30 days)" . PHP_EOL;
    echo "4. Try searching by transaction ID instead of userReference" . PHP_EOL;
    echo "5. Verify authentication credentials are correct" . PHP_EOL;
}

// Execute the analysis
header('Content-Type: text/plain');
analyzeTransactionStatus();