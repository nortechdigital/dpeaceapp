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

// Send API request and return both formatted and raw data
function sendRequest($payload) {
    global $apiEndpoint;
    
    // Store the raw request payload
    $rawRequest = json_encode($payload, JSON_PRETTY_PRINT);
    
    $ch = curl_init($apiEndpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HEADER => true // Include headers in output
    ]);
    
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        throw new Exception("CURL Error: " . curl_error($ch));
    }
    curl_close($ch);
    
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    $result = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON response");
    }
    
    return [
        'raw_request' => $rawRequest,
        'raw_response' => [
            'headers' => $headers,
            'status_code' => $httpCode,
            'body' => $body
        ],
        'data' => $result
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
    
    $authPayload = [
        'auth' => [
            'username' => $username,
            'salt' => $authSalt,
            'password' => $authHash,
            'signature' => ''
        ],
        'version' => 5,
        'command' => 'getBalance'
    ];
    
    echo "=== RAW AUTHENTICATION REQUEST ===" . PHP_EOL;
    echo json_encode($authPayload, JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;
    
    $authResponse = sendRequest($authPayload);
    
    echo "=== RAW AUTHENTICATION RESPONSE ===" . PHP_EOL;
    echo "Status Code: {$authResponse['raw_response']['status_code']}" . PHP_EOL;
    echo "Headers: " . PHP_EOL . $authResponse['raw_response']['headers'] . PHP_EOL;
    echo "Body: " . PHP_EOL . json_encode($authResponse['raw_response']['body'], JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;
    
    if ($authResponse['data']['status']['type'] !== 0) {
        throw new Exception("Authentication failed: {$authResponse['data']['status']['name']}");
    }
    
    echo "✅ Authentication successful!" . PHP_EOL;
    echo "Current balance: {$authResponse['data']['result']['value']} {$authResponse['data']['result']['currency']}" . PHP_EOL . PHP_EOL;

    // ===== 2. Get Product Details =====
    $productId = 1; // Example product ID (MTN Top-Up)
    $productSalt = generateSalt();
    
    $productPayload = [
        'auth' => [
            'username' => $username,
            'salt' => $productSalt,
            'password' => calculateHash($password, $productSalt),
            'signature' => ''
        ],
        'version' => 5,
        'command' => 'getProduct',
        'productId' => $productId
    ];
    
    echo "=== RAW PRODUCT REQUEST ===" . PHP_EOL;
    echo json_encode($productPayload, JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;
    
    $productResponse = sendRequest($productPayload);
    
    echo "=== RAW PRODUCT RESPONSE ===" . PHP_EOL;
    echo "Status Code: {$productResponse['raw_response']['status_code']}" . PHP_EOL;
    echo "Headers: " . PHP_EOL . $productResponse['raw_response']['headers'] . PHP_EOL;
    echo "Body: " . PHP_EOL . json_encode(json_decode($productResponse['raw_response']['body']), JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;
    
    // ===== 3. Display Formatted Results =====
    echo "=== FORMATTED PRODUCT DETAILS ===" . PHP_EOL;
    if (!isset($productResponse['data']['result'])) {
        throw new Exception("Invalid product response structure");
    }
    
    $product = $productResponse['data']['result']['product'];
    $operator = $productResponse['data']['result']['operator'];
    $currency = $productResponse['data']['result']['currency'];
    
    echo "ID: {$product['id']}" . PHP_EOL;
    echo "Name: {$product['name']}" . PHP_EOL;
    echo "Type: {$product['productType']['name']} (ID: {$product['productType']['id']})" . PHP_EOL;
    echo "Price Type: {$product['priceType']}" . PHP_EOL;
    
    if ($product['priceType'] === 'range') {
        echo "Price Range:" . PHP_EOL;
        echo "- Min: {$product['price']['min']['user']} {$currency['user']}" . PHP_EOL;
        echo "- Max: {$product['price']['max']['user']} {$currency['user']}" . PHP_EOL;
    } else {
        echo "Price: {$product['price']['user']} {$currency['user']}" . PHP_EOL;
    }
    
    echo PHP_EOL . "Operator:" . PHP_EOL;
    echo "- ID: {$operator['id']}" . PHP_EOL;
    echo "- Name: {$operator['name']}" . PHP_EOL;
    
    echo PHP_EOL . "Currency:" . PHP_EOL;
    echo "- User: {$currency['user']}" . PHP_EOL;
    echo "- Operator: {$currency['operator']}" . PHP_EOL;
    
    if (!empty($product['extraParameters'])) {
        echo PHP_EOL . "Required Parameters:" . PHP_EOL;
        foreach ($product['extraParameters'] as $param => $details) {
            echo "- {$details['name']}: " . ($details['mandatory'] ? "Required" : "Optional") . PHP_EOL;
            if (!empty($details['values'])) {
                echo "  Options: " . implode(", ", $details['values']) . PHP_EOL;
            }
            if (!empty($details['tip'])) {
                echo "  Tip: {$details['tip']}" . PHP_EOL;
            }
        }
    }

} catch (Exception $e) {
    echo PHP_EOL . "❌ Error: " . $e->getMessage() . PHP_EOL;
    
    // Debug information
    if (isset($productResponse)) {
        echo PHP_EOL . "Last Raw Response:" . PHP_EOL;
        echo json_encode($productResponse['raw_response'], JSON_PRETTY_PRINT) . PHP_EOL;
    }
    
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo PHP_EOL . "Authentication Debug:" . PHP_EOL;
        echo "Hash Calculation Test: " . sha1('testsalt' . sha1($password)) . " (using salt 'testsalt')" . PHP_EOL;
    }
}
?>