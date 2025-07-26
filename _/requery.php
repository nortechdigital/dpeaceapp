<?php
// ac_requery_transaction.php
session_start();
require "../conn.php";
require "../_/ac_config.php";

// 1. Verify session and request method
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Session expired. Please login again.']));
}

// 2. Get request ID from query parameters or POST data
$request_id = $_GET['request_id'] ?? $_POST['request_id'] ?? null;
if (empty($request_id)) {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Transaction reference is required']));
}

// 3. Prepare API request
$endpoint = VAS2NETS_BASE_URL . VAS2NETS_ENV_URI . "requery";
$payload = ['requestId' => $request_id];

// Handle special characters in password
$password = urlencode(VAS2NETS_PASSWORD);
$auth_string = base64_encode(VAS2NETS_USERNAME . ":" . $password);

$headers = [
    "Content-Type: application/json",
    "Authorization: Basic $auth_string"
];

// 4. Execute Requery
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $endpoint,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// 5. Handle Response
$response_data = json_decode($response, true) ?: [];

if ($http_code === 200) {
    // Update transaction status in database
    try {
        $stmt = $conn->prepare("UPDATE transactions 
                               SET status = :status, 
                                   response_data = :response_data,
                                   updated_at = NOW()
                               WHERE request_id = :request_id");
        
        $stmt->execute([
            ':status' => $response_data['data']['status'] ?? 'unknown',
            ':response_data' => json_encode($response_data),
            ':request_id' => $request_id
        ]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }

    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'Requery completed',
        'transaction_status' => $response_data['data']['status'] ?? null,
        'data' => $response_data
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => $response_data['message'] ?? 'Requery failed',
        'http_code' => $http_code,
        'response' => $response_data
    ]);
}