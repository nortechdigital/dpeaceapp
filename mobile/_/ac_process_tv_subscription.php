<?php
// ac_process_tv_subscription.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require "../conn.php";
require "../_/ac_config.php";

function showErrorAndRedirect($message, $page = 'cabletv') {
    echo '<!DOCTYPE html><html><head><script type="text/javascript">';
    echo 'alert("Error: ' . addslashes($message) . '");';
    echo 'window.location.href = "../?page=' . $page . '";';
    echo '</script></head><body></body></html>';
    exit;
}

function redirectToLogin() {
    showErrorAndRedirect("Session expired. Please login again.", "login");
}

// Validate session
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    showErrorAndRedirect("Invalid request method");
}

// Check wallet balance
$stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    showErrorAndRedirect("Wallet not found");
}

$row = $result->fetch_assoc();
$balance = (float)$row['balance'];

// Validate inputs
$required_fields = ['biller_id', 'bouquet_code', 'smartcard_number', 'selected_price', 'discounted_price'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        showErrorAndRedirect("Missing required field: $field");
    }
}

try {
    // Sanitize inputs
    $biller_id = htmlspecialchars($_POST['biller_id']);
    $bouquet_code = htmlspecialchars($_POST['bouquet_code']);
    $smartcard_number = htmlspecialchars($_POST['smartcard_number']);
    $amount = filter_var($_POST['selected_price'], FILTER_VALIDATE_FLOAT);
    $discount = filter_var($_POST['discounted_price'], FILTER_VALIDATE_FLOAT);

    if ($amount === false || $discount === false) {
        throw new Exception("Invalid amount or discount value");
    }

    Check if balance is sufficient (only for success/pending cases)
    if ($balance < $discount) {
        throw new Exception("Insufficient wallet balance");
    }

    // Prepare API request
    $endpoint = VAS2NETS_BASE_URL . VAS2NETS_ENV_URI . "tv/validate";
    $payload = [
        'billerId' => $biller_id,
        'bouquetCode' => $bouquet_code,
        'customerId' => $smartcard_number,
        'amount' => $amount,
        'requestId' => date('YmdHis') . mt_rand(1000, 9999)
    ];

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode(VAS2NETS_USERNAME . ':' . VAS2NETS_PASSWORD)
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new Exception("CURL error: $error");
    }

    $response_data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON response: " . json_last_error_msg());
    }

    if ($http_code !== 200) {
        throw new Exception("API request failed with HTTP code: $http_code");
    }

    if (!isset($response_data['data']['status'])) {
        throw new Exception("Invalid API response format");
    }

    // Get response details
    $status = strtolower($response_data['data']['status']);
    $message = $response_data['data']['message'] ?? 'Transaction processed';

    // Update wallet balance only for success/pending status
    if ($status === 'success' || $status === 'pending') {
        $new_balance = $balance - $discount;
        $update_sql = "UPDATE wallets SET balance = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("di", $new_balance, $_SESSION['user_id']);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update wallet balance");
        }
    }

    // Always log the transaction regardless of status
    $fullname = $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];
    $phone_number = $_SESSION['phone'];
    $product_description = 'Purchase of SWIFT Bundle';
    $type = 'TV Subscription Purchase';
    $detail = 'TV Subscription';
    $ref_id = date('YmdHis');
    $profit = 0;

    $insert_sql = "INSERT INTO transactions (user_id, fullname, phone_number, product_description, 
                  amount, status, type, detail, transaction_ref, profit) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare transaction statement");
    }

    $stmt->bind_param("isssdsssss", $_SESSION['user_id'], $fullname, $phone_number, 
                      $product_description, $discount, $status, $type, $detail, $ref_id, $profit);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to log transaction: " . $stmt->error);
    }

    $transaction_id = $stmt->insert_id;
    $stmt->close();

    // Handle different statuses
    if ($status === 'success' || $status === 'pending') {
        // Redirect to receipt page for successful or pending transactions
        header('Location: ../?page=receipt&id=' . $transaction_id);
    } else {
        // For failed transactions, show error but transaction is already logged
        showErrorAndRedirect($message);
    }
    exit;

} catch (Exception $e) {
    showErrorAndRedirect($e->getMessage());
}
?>