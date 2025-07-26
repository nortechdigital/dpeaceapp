<?php
// server_a.php - Hosted on dpeaceapp.com (Server 1)

// Configuration
define('SERVER_B_URL', 'https://server.dpeaceapp.com/server_b.php');
define('SERVER_A_CALLBACK_URL', 'https://dpeaceapp.com/server_a_callback.php');

// Data to send
$data = [
    'message' => 'Hello from dpeaceapp.com (Server 1)',
    'timestamp' => time(),
    'source' => 'dpeaceapp.com'
];

// Send data to Server B
function sendToServerB($data) {
    $ch = curl_init(SERVER_B_URL);
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Callback-URL: ' . SERVER_A_CALLBACK_URL,
            'X-Api-Key: YOUR_SECRET_KEY'  // Add authentication
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_SSL_VERIFYPEER => true,  // Enable for production
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception("Server B returned HTTP code: $httpCode");
    }
    
    return json_decode($response, true);
}

// Main execution
try {
    echo "dpeaceapp.com: Sending to server.dpeaceapp.com: " . json_encode($data) . "\n";
    $immediateResponse = sendToServerB($data);
    echo "dpeaceapp.com: Received immediate response from server.dpeaceapp.com: " . 
         json_encode($immediateResponse) . "\n";
    
    echo "dpeaceapp.com: Waiting for callback from server.dpeaceapp.com...\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    file_put_contents('error_log.txt', date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n", FILE_APPEND);
}