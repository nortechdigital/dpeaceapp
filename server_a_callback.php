<?php
// server_a_callback.php - Hosted on dpeaceapp.com

header('Content-Type: application/json');

// Verify API key
$headers = getallheaders();
if (!isset($headers['X-Api-Key']) || $headers['X-Api-Key'] !== 'YOUR_SECRET_KEY') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get the POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON received']);
    exit;
}

// Process the callback
file_put_contents('callback_log.txt', 
    date('Y-m-d H:i:s') . " - From: server.dpeaceapp.com - Data: " . json_encode($data) . "\n", 
    FILE_APPEND);

// Send response
echo json_encode([
    'status' => 'success',
    'received_at' => time(),
    'server' => 'dpeaceapp.com',
    'message' => 'Callback processed successfully'
]);