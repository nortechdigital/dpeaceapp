<?php
// ARTX API Configuration
$apiEndpoint = 'https://artx.sochitel.com/staging.php';
$username = 'testUser1';
$password = 'Test123!';

// Generate unique salt
function generateSalt() {
    return bin2hex(random_bytes(20));
}

// Calculate password hash
function calculateHash($password, $salt) {
    return sha1($salt . sha1($password));
}

// Send API request and return raw request/response
function sendRequest($payload) {
    global $apiEndpoint;
    
    // Prepare raw request data
    $rawRequest = json_encode($payload, JSON_PRETTY_PRINT);
    
    $ch = curl_init($apiEndpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $rawRequest,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HEADER => true // Include headers in response
    ]);
    
    $rawResponse = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    return [
        'request' => $rawRequest,
        'response' => $rawResponse,
        'error' => $curlError
    ];
}

// ===== MAIN EXECUTION =====
try {
    // Clear output buffer
    ob_clean();
    header('Content-Type: text/plain');

    // ===== 1. Authenticate =====
    $authSalt = generateSalt();
    $authHash = calculateHash($password, $authSalt);
    
    $authRequest = [
        'auth' => [
            'username' => $username,
            'salt' => $authSalt,
            'password' => $authHash,
            'signature' => ''
        ],
        'version' => 5,
        'command' => 'getBalance'
    ];
    
    $authResult = sendRequest($authRequest);
    
    echo "=== RAW AUTHENTICATION REQUEST ===" . PHP_EOL;
    echo $authResult['request'] . PHP_EOL . PHP_EOL;
    echo "=== RAW AUTHENTICATION RESPONSE ===" . PHP_EOL;
    echo $authResult['response'] . PHP_EOL . PHP_EOL;

    // ===== 2. Get Operators =====
    $operatorSalt = generateSalt();
    $operatorRequest = [
        'auth' => [
            'username' => $username,
            'salt' => $operatorSalt,
            'password' => calculateHash($password, $operatorSalt),
            'signature' => ''
        ],
        'version' => 5,
        'command' => 'getOperators',
        'productType' => 1,
        'country' => 'NG'
    ];
    
    $operatorResult = sendRequest($operatorRequest);
    
    echo "=== RAW OPERATOR REQUEST ===" . PHP_EOL;
    echo $operatorResult['request'] . PHP_EOL . PHP_EOL;
    echo "=== RAW OPERATOR RESPONSE ===" . PHP_EOL;
    echo $operatorResult['response'] . PHP_EOL . PHP_EOL;

    // ===== 3. Process Airtime =====
    $transactionSalt = generateSalt();
    $transactionRequest = [
        'auth' => [
            'username' => $username,
            'salt' => $transactionSalt,
            'password' => calculateHash($password, $transactionSalt),
            'signature' => ''
        ],
        'version' => 5,
        'command' => 'execTransaction',
        'operator' => 1,
        'msisdn' => '2348065615684',
        'amount' => 100,
        'productId' => 1,
        'userReference' => 'AIRTIME_' . time()
    ];
    
    $transactionResult = sendRequest($transactionRequest);
    
    echo "=== RAW TRANSACTION REQUEST ===" . PHP_EOL;
    echo $transactionResult['request'] . PHP_EOL . PHP_EOL;
    echo "=== RAW TRANSACTION RESPONSE ===" . PHP_EOL;
    echo $transactionResult['response'] . PHP_EOL;

} catch (Exception $e) {
    echo PHP_EOL . "❌ Error: " . $e->getMessage() . PHP_EOL;
}
?>