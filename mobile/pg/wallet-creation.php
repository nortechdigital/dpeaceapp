<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set("Africa/Lagos");

// 1. Load Configuration
$configPath = '/home/dpeaceapp/public_html/env.php';
if (!file_exists($configPath)) {
    die("Error: Config file missing at $configPath");
}
$config = require $configPath;

// Required config keys
$requiredConfig = ['OPAY_API_KEY', 'OPAY_MERCHANT_ID', 'OPAY_CLIENT_AUTH_KEY'];
foreach ($requiredConfig as $key) {
    if (empty($config[$key])) {
        die("Error: Missing config key: $key");
    }
}

// 2. Start Session and Validate Session Data
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$requiredSession = ['firstname', 'lastname', 'email', 'phone'];
foreach ($requiredSession as $key) {
    if (empty($_SESSION[$key])) {
        die("Error: Missing session data: $key");
    }
}

// 3. Prepare Request Data
$timestamp = time();
$nonceStr = bin2hex(random_bytes(16));      // Random string
$refId = 'DPA' . date('ymdhis');             // Unique reference

// Generate signature
function generateSignature($config, $timestamp, $nonceStr, $refId) {
    $signString = implode('|', [
        $config['OPAY_MERCHANT_ID'],
        $timestamp,
        $nonceStr,
        $refId,
        $config['OPAY_API_KEY']
    ]);
    return hash_hmac('sha256', $signString, $config['OPAY_CLIENT_AUTH_KEY']);
}

$signature = generateSignature($config, $timestamp, $nonceStr, $refId);

// Build request body
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

// 4. Prepare Headers
$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $config['OPAY_API_KEY'],
    'MerchantId: ' . $config['OPAY_MERCHANT_ID'],
    'clientAuthKey: ' . $config['OPAY_CLIENT_AUTH_KEY'],
    'version: 1.0',
    'bodyFormat: 1',
    'timestamp: ' . $timestamp,
    'nonceStr: ' . $nonceStr
];

// 5. Send Request via cURL
$ch = curl_init('https://payapi.opayweb.com/api/v2/third/depositcode/generateStaticDepositCode');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($requestBody),
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_VERBOSE => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    die("cURL Error: " . curl_error($ch));
}

curl_close($ch);

// 6. Process Response
$responseData = json_decode($response, true);

// Debug Output
echo "<h2>API Response</h2>";
echo "<p><strong>Timestamp (ms):</strong> {$timestamp} (" . date('c', $timestamp / 1000) . ")</p>";

if ($httpCode == 200 && ($responseData['code'] ?? '') === '00000') {
    echo "<div style='color:green;'>Success! Wallet created:</div>";
    echo "<pre>" . print_r($responseData['data'], true) . "</pre>";
} else {
    echo "<div style='color:red;'>API Error (Code: " . ($responseData['code'] ?? 'N/A') . "):</div>";
    echo "<pre>" . print_r($responseData, true) . "</pre>";

    // Request Debug
    echo "<h3>Request Debug</h3>";
    echo "<pre>Headers:\n" . print_r($headers, true) . "</pre>";
    echo "<pre>Request Body:\n" . json_encode($requestBody, JSON_PRETTY_PRINT) . "</pre>";
}
?>
