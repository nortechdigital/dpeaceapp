<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();
include "../conn.php"; // Database connection & test_input function

require_once "../glo/GloNigeriaClient.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token($_POST['csrf_token'] ?? '');
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

// User, phone and balance check
try {
    $phone_number = $msisdn = test_input($_POST['phone_number']) ?? '';
    $plan_id = test_input($_POST['plan_id']) ?? '';
    $amount = (int)test_input($_POST['price'] ?? 0);
    $plan_name = test_input($_POST['plan_name'] ?? '');
    $discount = test_input($_POST['discounted_price'] ?? $amount); // Default to full amount if no discount
    
    // Get user wallet balance
    $sql = "SELECT * FROM wallets WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "User not found!";
        $stmt->close();
        die(header('Location: ' . $_SERVER['HTTP_REFERER']));
    }

    // Phone number validation
    $ported_number = test_input($_POST['ported_number'] ?? 'false');
    $billerId = 'GLO';
    
    // Validate phone number format first
    if (!preg_match('/^0[7-9][0-1]\d{8}$/', $phone_number)) {
        $_SESSION['error'] = "Invalid phone number format. Please enter a valid Nigerian phone number.";
        $stmt->close();
        die(header('Location: ' . $_SERVER['HTTP_REFERER']));
    }
    
    $phone_prefix = substr($phone_number, 0, 4);
    if ($ported_number == 'false') {
        if (preg_match('/^(0805|0807|0705|0815|0811|0905)$/', $phone_prefix)) {
            if ($billerId !== 'GLO') {
                $_SESSION['error'] = "Invalid phone number for selected provider.";
                $stmt->close();
                die(header('Location: ' . $_SERVER['HTTP_REFERER']));
            }
        } else {
            $_SESSION['error'] = "Invalid phone number for GLO provider.";
            $stmt->close();
            die(header('Location: ' . $_SERVER['HTTP_REFERER']));
        }
    }

    $row = $result->fetch_assoc();
    $balance = $row['balance'];

    if ($balance < $discount) {
        $_SESSION['error'] = "Insufficient balance. Please top up your account!";
        $stmt->close();
        die(header('Location: ' . $_SERVER['HTTP_REFERER']));
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    die(header('Location: ' . $_SERVER['HTTP_REFERER']));
}

try {
    // Generate a temporary reference first
    $temp_ref = 'GLO_' . uniqid();
    $description = "Purchase of GLO-DATA: $plan_name for N$amount";
    $detail = "GLO-DATA: $plan_name";
    $relationId = $_SESSION['user_id'] ?? 'CUST' . uniqid();
    $fullname = ($_SESSION['firstname'] . ' ' . $_SESSION['lastname']) ?? 'Unknown User';
    $cust_discount = $amount - $discount;
    
    // Record the transaction as "PENDING" with temporary reference
    $status = "PENDING";
    $current_balance = $balance;
    $new_balance = $balance - $discount;
    
    $sql = "INSERT INTO transactions (user_id, fullname, phone_number, transaction_ref, product_description, amount, status, profit, type, detail, cust_discount, current_balance, new_balance) VALUES
    ('$relationId', '$fullname', '$phone_number', '$temp_ref', '$description', '$discount', '$status', '0', 'Data Purchase', '$detail', '$cust_discount', '$current_balance', '$new_balance')";
    
    if (!$conn->query($sql)) {
        $_SESSION['error'] = "Failed to record transaction: " . $conn->error;
        die(header('Location: ' . $_SERVER['HTTP_REFERER']));
    }
    
    $transaction_id = $conn->insert_id;
    
    // Update the wallet balance
    if (!$conn->query("UPDATE wallets SET balance='$new_balance' WHERE user_id=$relationId")) {
        $_SESSION['error'] = "Failed to update wallet balance: " . $conn->error;
        die(header('Location: ' . $_SERVER['HTTP_REFERER']));
    }

    $gloClient = new GloNigeriaClient();
    
    $response = $gloClient->requestTopup(
        $msisdn,
        $plan_id,
        $amount,
        'NGN',
        'DATA_BUNDLE'
    );
    
    if ($response->return->resultCode == 0) {
        // Get the actual Glo reference number
        $glo_reference = $response->return->ersReference;
        
        // Update transaction with Glo's reference
        $status = "SUCCESS";
        $update_sql = "UPDATE transactions SET 
                        status = '$status',
                        transaction_ref = '$glo_reference'
                        WHERE id = $transaction_id";
        
        if (!$conn->query($update_sql)) {
            error_log("Failed to update transaction reference: " . $conn->error);
        }
        
        die(header('Location: ../?page=receipt&id=' . $transaction_id));
    } else {
        // Handle error - refund the amount
        $conn->query("UPDATE wallets SET balance='$balance' WHERE user_id=$relationId");
        
        $code = $response->return->resultCode;
        $msg = $response->return->resultDescription;
        $status = "FAILED";
        
        // Update transaction status but keep the temporary reference
        $update_sql = "UPDATE transactions SET 
                        status = '$status',
                        new_balance = '$balance'
                        WHERE id = $transaction_id";
        $conn->query($update_sql);
        
        $_SESSION['error'] = "Error ($code): $msg";
    }
} catch (Exception $e) {
    // Error handling remains the same
    if (isset($relationId)) {
        $conn->query("UPDATE wallets SET balance='$balance' WHERE user_id=$relationId");
    }
    
    if (isset($transaction_id)) {
        $update_sql = "UPDATE transactions SET 
                        status = 'FAILED',
                        new_balance = '$balance'
                        WHERE id = $transaction_id";
        $conn->query($update_sql);
    }
    
    $_SESSION['error'] = "API Error: " . $e->getMessage();
}
die(header('Location: ' . $_SERVER['HTTP_REFERER']));