<?php
session_start();
include "../conn.php";
include "../_/ac_config.php";

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'User session not found';
    die(header('Location: ' . $_SERVER['HTTP_REFERER']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validate_csrf_token($_POST['csrf_token'] ?? '');
    $required = ['cust_reference', 'hidden_amount'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "$field is required";
            die(header('Location: ' . $_SERVER['HTTP_REFERER']));
        }
    }
}

// Check last transaction time
try {
    $user_id = $_SESSION['user_id'] ?? 0;
    $stmt = $conn->prepare("SELECT created_at FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $last_transaction = $result->fetch_assoc();
        $last_time = strtotime($last_transaction['created_at']);
        $current_time = time();
        
        if (($current_time - $last_time) < 60) { // 60 seconds = 1 minute
            $_SESSION['error'] = "Please try again after 1 minute.";
            die(header('Location: ' . $_SERVER['HTTP_REFERER']));
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error checking transaction history: " . $e->getMessage();
    die(header('Location: ' . $_SERVER['HTTP_REFERER']));
}

// Get form data
$customer_id = $cust_reference = trim($_POST['cust_reference']);
$amount = (float)$_POST['hidden_amount'];
$discount = (float)$_POST['discounted_price'];
$fullname = $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];
$detail = "Purchase of SWIFT Bundle";
$ref_id = date('YmdHis');
$product_description = preg_replace('/[^A-Za-z0-9 ]/', '', $_POST['hidden_plan_name']);
$type = 'SWIFT Bundle Purchase';
$cust_discount = $amount - $discount;

// Check wallet balance
$stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('User wallet not found');window.location.href='../?page=data';</script>";
    die(header('Location: ' . $_SERVER['HTTP_REFERER']));
}

$row = $result->fetch_assoc();
$balance = $row['balance'];

if ($balance < $discount) {
    echo "<script>alert('Insufficient balance');window.location.href='../?page=data';</script>";
    die(header('Location: ' . $_SERVER['HTTP_REFERER']));
}

// Record transaction first
$current_status = "PENDING";
$current_balance = $balance;
$new_balance = $balance - $discount;

$insert_sql = "INSERT INTO transactions (user_id, fullname, phone_number, product_description, amount, status, type, detail, transaction_ref, profit, cust_discount, current_balance, new_balance) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_sql);
if ($stmt) {
    $stmt->bind_param("isssdssssdddd", $_SESSION['user_id'], $fullname, $customer_id, $product_description, $discount, $current_status, $type, $detail, $ref_id, 0, $cust_discount, $current_balance, $new_balance);
    if (!$stmt->execute()) {
        error_log("Transaction logging failed: " . $stmt->error);
        echo "<script>alert('System error');window.location.href='../?page=data';</script>";
        die(header('Location: ' . $_SERVER['HTTP_REFERER']));
    }
    $transaction_id = $stmt->insert_id;
    $stmt->close();
} else {
    error_log("Prepare failed: " . $conn->error);
    echo "<script>alert('System error');window.location.href='../?page=data';</script>";
    die(header('Location: ' . $_SERVER['HTTP_REFERER']));
}

// Deduct from wallet
$update_stmt = $conn->prepare("UPDATE wallets SET balance = ? WHERE user_id = ?");
$update_stmt->bind_param("di", $new_balance, $_SESSION['user_id']);
if (!$update_stmt->execute()) {
    error_log("Balance update failed: " . $update_stmt->error);
    // Continue with payment processing despite the error
}
$update_stmt->close();

// Validate customer with SWIFT
$validation_endpoint = SWIFT_BASE_URL . "Username=" . SWIFT_USERNAME . "&" . "Password=" . SWIFT_PASSWORD . "&" . "Partner=" . SWIFT_PARTNER . "&customer_id=" . urlencode($customer_id);
    
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $validation_endpoint,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    // Refund and update transaction status
    $conn->query("UPDATE wallets SET balance='$balance' WHERE user_id=".$_SESSION['user_id']);
    $conn->query("UPDATE transactions SET status='FAILED', new_balance='$balance' WHERE id=$transaction_id");
    echo "<script>alert('Failed to validate customer');window.location.href='../?page=data';</script>";
    exit;
}

// Parse the XML response
$xml = simplexml_load_string($response);
if (!$xml || !isset($xml->Customer)) {
    // Refund and update transaction status
    $conn->query("UPDATE wallets SET balance='$balance' WHERE user_id=".$_SESSION['user_id']);
    $conn->query("UPDATE transactions SET status='FAILED', new_balance='$balance' WHERE id=$transaction_id");
    echo "<script>alert('Invalid response from SWIFT');window.location.href='../?page=data';</script>";
    exit;
}

$customer = $xml->Customer;
$status_code = (string) $customer->StatusCode;
$status_description = (string) $customer->StatusDescription;

if ($status_code !== "0") {
    // Refund and update transaction status
    $conn->query("UPDATE wallets SET balance='$balance' WHERE user_id=".$_SESSION['user_id']);
    $conn->query("UPDATE transactions SET status='FAILED', new_balance='$balance' WHERE id=$transaction_id");
    echo "<script>alert('Invalid customer: $status_description');window.location.href='../?page=data';</script>";
    exit;
}

// Process payment
$log_id = date('YmdHis');
$payment_date = date('Y-m-d H:i:s');
$xml_request = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<PaymentNotificationRequest>
    <Payments>
        <Payment>
            <PaymentLogId>{$log_id}</PaymentLogId>
            <CustReference>{$cust_reference}</CustReference>
            <Amount>{$amount}</Amount>
            <PaymentMethod>WEB</PaymentMethod>
            <PaymentDate>{$payment_date}</PaymentDate>
            <IsReversal>false</IsReversal>
            <PaymentItems>
                <PaymentItem>
                    <ItemName>Service</ItemName>
                    <ItemCode>Service</ItemCode>
                    <ItemAmount>{$amount}</ItemAmount>
                </PaymentItem>
            </PaymentItems>
        </Payment>
    </Payments>
</PaymentNotificationRequest>
XML;

$url = SWIFT_BASE_URL1 . "Username=" . SWIFT_USERNAME ."&". "Password=" . SWIFT_PASSWORD ."&". "Partner=" . SWIFT_PARTNER;
$headers = [
    "Content-Type: application/xml",
    "Content-Length: " . strlen($xml_request)
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $xml_request,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 180
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    // Refund and update transaction status
    $conn->query("UPDATE wallets SET balance='$balance' WHERE user_id=".$_SESSION['user_id']);
    $conn->query("UPDATE transactions SET status='FAILED', new_balance='$balance' WHERE id=$transaction_id");
    echo "<script>alert('Failed to process payment: $curl_error');window.location.href='../?page=data';</script>";
    exit;
}

$response_xml = simplexml_load_string($response);
$status = (string)$response_xml->Payments->Payment->Status ?? null;
$status_desc = (string)$response_xml->Payments->Payment->StatusDesc ?? null;

if ($status !== "0") {
    // Refund and update transaction status
    $conn->query("UPDATE wallets SET balance='$balance' WHERE user_id=".$_SESSION['user_id']);
    $conn->query("UPDATE transactions SET status='FAILED', new_balance='$balance' WHERE id=$transaction_id");
    echo "<script>alert('Transaction failed: $status_desc');window.location.href='../?page=data';</script>";
    exit;
}

// Payment successful - no need to refund
$conn->query("UPDATE transactions SET status='SUCCESS' WHERE id=$transaction_id");
header('Location: ../?page=receipt&id=' . $transaction_id);
die(header('Location: ' . $_SERVER['HTTP_REFERER']));
?>