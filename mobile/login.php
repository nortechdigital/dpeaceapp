<?php
// login.php - Hosted on dpeaceapp.com

header('Content-Type: application/json');

// Configuration
define('AUTH_SERVER_URL', 'https://server.dpeaceapp.com/authenticate.php');
define('API_KEY', 'dp_1234567890abcdef1234567890abcdef'); // In production, store securely

// Simulate login form submission
$loginData = [
    'username' => $_POST['username'] ?? 'test_user',
    'password' => $_POST['password'] ?? 'secure_password123',
    'ip_address' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
];

function sendLoginRequest($data) {
    $ch = curl_init(AUTH_SERVER_URL);
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Api-Key: ' . API_KEY,
            'X-Request-ID: ' . bin2hex(random_bytes(8))
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        error_log('Curl error: ' . curl_error($ch));
        curl_close($ch);
        return ['error' => 'Connection failed'];
    }
    
    curl_close($ch);
    return json_decode($response, true) ?? ['error' => 'Invalid response'];
}

// Process login
$authResult = sendLoginRequest($loginData);

if (isset($authResult['error'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication failed',
        'details' => $authResult['error']
    ]);
} else {
    // Successful authentication
    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful',
        'user' => $authResult['user'] ?? null,
        'token' => $authResult['token'] ?? null,
        'expires' => $authResult['expires'] ?? null
    ]);
}