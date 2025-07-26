<?php
// 1. Enable Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Force Lagos Timezone (Opay requires Nigeria time)
date_default_timezone_set('Africa/Lagos');

// 3. Load Configuration
$configPath = '/home/dpeaceapp/public_html/env.php';
if (!file_exists($configPath)) {
    die("Error: Config file missing at $configPath");
}
$config = require $configPath;

// 4. Validate Required Config
$requiredConfig = ['OPAY_API_KEY', 'OPAY_MERCHANT_ID', 'OPAY_CLIENT_AUTH_KEY'];
foreach ($requiredConfig as $key) {
    if (empty($config[$key])) {
        die("Error: Missing config key: $key");
    }
}

// 5. Start Session (Safely) and Validate Session Variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$requiredSession = ['firstname', 'lastname', 'email', 'phone'];
foreach ($requiredSession as $key) {
    if (empty($_SESSION[$key])) {
        die("Error: Missing session data: $key");
    }
}

// 6. Prepare Timestamp, Nonce, Reference
$timestamp = number_format(microtime(true) * 1000, 0, '.', '');
$nonceStr = bin2hex(random_bytes(16));   
$refId = 'DPA' . date('ymdhis');  

// 7. Enhanced Signature Generation with Validation
function generateSignature($config, $timestamp, $nonceStr, $refId) {
    // Validate inputs
    if (strlen($timestamp) !== 13 || !is_numeric($timestamp)) {
        die("Invalid timestamp format");
    }
    if (strlen($nonceStr) !== 32) {
        die("Invalid nonce format");
    }
    if (strlen($config['OPAY_MERCHANT_ID']) < 5) {
        die("Invalid merchant ID");
    }

    $components = [
        $config['OPAY_MERCHANT_ID'],
        $timestamp,
        $nonceStr,
        $refId,
        $config['OPAY_API_KEY']
    ];
    
    // Verify no component is empty
    foreach ($components as $component) {
        if (empty($component)) {
            die("Empty component in signature generation");
        }
    }
    
    $signString = implode('|', $components);
    echo "<h3>Signature Components</h3><pre>" . print_r($components, true) . "</pre>";
    echo "<h3>Signature String (before HMAC)</h3><pre>{$signString}</pre>";
    
    $signature = hash_hmac('sha256', $signString, $config['OPAY_CLIENT_AUTH_KEY']);
    if (strlen($signature) !== 64) {
        die("Invalid signature generated");
    }
    
    return [
        'signature' => $signature,
        'signString' => $signString
    ];
}

// 8. Generate the Signature
$signatureResult = generateSignature($config, $timestamp, $nonceStr, $refId);
$signature = $signatureResult['signature'];

// 9. Show Signature
echo "<h3>Signature (after HMAC)</h3><pre>{$signature}</pre>";

// 10. Prepare JSON Request Body
$requestBody = [
    'opayMerchantId' => $config['OPAY_MERCHANT_ID'],
    'name' => $_SESSION['firstname'] . ' ' . $_SESSION['lastname'],
    'refId' => $refId,
    'email' => $_SESSION['email'],
    'phone' => $_SESSION['phone'],
    'accountType' => 'Merchant',
    'sendPassWordFlag' => 'Y',
    'paramContent' => json_encode([
        'timestamp' => $timestamp,
        'nonceStr' => $nonceStr
    ]),
    'sign' => $signature
];

// 11. Prepare HTTP Headers
$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $config['OPAY_API_KEY'],
    'MerchantId: ' . $config['OPAY_MERCHANT_ID'],
    'ClientAuthKey: ' . $config['OPAY_CLIENT_AUTH_KEY'],
    'Version: 1.0',
    'BodyFormat: 1',
    'Timestamp: ' . $timestamp,
    'NonceStr: ' . $nonceStr
];

// 12. Initialize cURL Request
$ch = curl_init('https://payapi.opayweb.com/api/v2/third/depositcode/generateStaticDepositCode');
$logFile = fopen('/tmp/curl_debug.log', 'w'); 

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($requestBody),
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_VERBOSE => true,
    CURLOPT_STDERR => $logFile
]);

// 13. Execute and Process Response
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
fclose($logFile);

if (curl_errno($ch)) {
    die("cURL Error: " . curl_error($ch));
}

curl_close($ch);

// 14. Decode and Display Response
$responseData = json_decode($response, true);

echo "<h2>API Response</h2>";
echo "<p><strong>Timestamp (ms):</strong> {$timestamp} (" . date('c', $timestamp / 1000) . ")</p>";

if ($httpCode === 200 && ($responseData['code'] ?? '') === '00000') {
    echo "<div style='color:green;'>✅ Success! Wallet created:</div>";
    echo "<pre>" . print_r($responseData['data'], true) . "</pre>";
} else {
    echo "<div style='color:red;'>❌ API Error (Code: " . ($responseData['code'] ?? 'N/A') . "):</div>";
    echo "<pre>" . print_r($responseData, true) . "</pre>";
    echo "<h3>🔍 Request Debug</h3>";
    echo "<pre>Headers:\n" . print_r($headers, true) . "</pre>";
    echo "<pre>Request Body:\n" . json_encode($requestBody, JSON_PRETTY_PRINT) . "</pre>";
    echo "<p>cURL debug output saved to: <code>/tmp/curl_debug.log</code></p>";
}
?>