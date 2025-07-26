<?php
$url = 'https://dpeaceapp.com/opay_webhook.php';

// Correct headers to match the expected names and values
$headers = [
    'Content-Type: application/json',
    'X-Opay-Tranid: 123456789', 
    'merchantId: 256625060262101' 
];

// Simulated payload
$data = [
    'status' => 'SUCCESS',
    'transactionId' => 'TRAN123456789',
    'depositCode' => '6129188935',
    'refId' => 'REF123',
    'depositTime' => date('Y-m-d\TH:i:s\Z'),
    'depositAmount' => 10000.00,
    'currency' => 'NGN',
    'errorCode' => '0',
    'errorMsg' => '',
    'formatDateTime' => date('Y-m-d\TH:i:s\Z'),
    'orderNo' => 'ORD123',
    'notes' => 'Test transaction'
];

// Validate headers and payload
if (empty($headers) || empty($data)) {
    die("Error: Headers or payload is missing.\n");
}

// Initialize cURL
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLINFO_HEADER_OUT => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_SSL_VERIFYPEER => true, // Enable SSL verification for production
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$sentHeaders = curl_getinfo($ch, CURLINFO_HEADER_OUT);

// Debug output
echo "=== Request Headers ===\n";
echo $sentHeaders;
echo "\n=== Request Body ===\n";
echo json_encode($data, JSON_PRETTY_PRINT);
echo "\n\n=== Response ===\n";
echo "HTTP Code: $httpCode\n";
echo $response;

curl_close($ch);
?>