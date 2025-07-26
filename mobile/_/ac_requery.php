<?php
session_start();
require "../conn.php";
require "../_/ac_config.php";

// Validate session and request method
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Session expired. Please login again.']));
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Invalid request method']));
}

// Get request ID
$request_id = $_POST['request_id'] ?? null;
if (empty($request_id)) {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Transaction reference is required']));
}

// Fetch transaction details
$query = "SELECT * FROM transactions WHERE request_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $request_id);
$stmt->execute();
$result = $stmt->get_result();
$transaction = $result->fetch_assoc();
$stmt->close();
if (!$transaction) {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Transaction not found']));
}
print_r($transaction);
// Prepare API request
$endpoint = "https://b2bapi.v2napi.com/dev/requery?requestId=$request_id";
$password = VAS2NETS_PASSWORD;
$username = VAS2NETS_USERNAME;
$auth_string = base64_encode($username . ":" . $password);

$headers = [
    "Content-Type: application/json",
    "Authorization: Basic $auth_string"
];

// Execute Requery
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $endpoint,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPGET => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

print($response);die;
// Handle Response
$response_data = json_decode($response, true) ?: [];
if ($http_code === 200 && isset($response_data['data'])) {
    $transaction_status = $response_data['data']['status'] ?? 'unknown';
    $user_id = $_SESSION['user_id'];
    $amount = $transaction['amount'];

    if ($transaction_status === 'success') {
        // Update transaction status
        $update_query = "UPDATE transactions SET status = 'success' WHERE request_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("s", $request_id);
        $stmt->execute();
        $stmt->close();

    } elseif ($transaction_status === 'failed') {
        // Update transaction status
        $update_query = "UPDATE transactions SET status = 'failed' WHERE request_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("s", $request_id);
        $stmt->execute();
        $stmt->close();

        // Refund user balance
        $update_balance_query = "UPDATE wallet SET balance = balance + ? WHERE id = ?";
        $stmt = $conn->prepare($update_balance_query);
        $stmt->bind_param("di", $amount, $user_id);
        $stmt->execute();
        $stmt->close();

        // Log refund transaction
        $product_description = "Failed transaction refund";
        $type = "refund";
        $detail = "Refund for failed transaction";
        $log_query = "INSERT INTO transactions (user_id, amount, type, status, request_id, description, detail) VALUES (?, ?, ?, 'refund', ?, ?, ?)";
        $stmt = $conn->prepare($log_query);
        $stmt->bind_param("idssss", $user_id, $amount, $type, $request_id, $product_description, $detail);
        $stmt->execute();
        $stmt->close();
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => $response_data['message'] ?? 'Requery failed',
        'http_code' => $http_code,
        'response' => $response_data,
        'curl_error' => $curl_error
    ]);
}
?>