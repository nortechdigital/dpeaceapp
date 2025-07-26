<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "../conn.php";
require_once "../artx/ArtxApiClient.php";

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
        
        if (($current_time - $last_time) < 60) {
            $_SESSION['error'] = "Please try again after 1 minute.";
            die(header('Location: ' . $_SERVER['HTTP_REFERER']));
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error checking transaction history: " . $e->getMessage();
    die(header('Location: ' . $_SERVER['HTTP_REFERER']));
}

try {
    $operatorId = test_input($_POST['operator']);
    $accountId = test_input($_POST['account_value']);
    $amount = (int)test_input($_POST['amount'] ?? 0);
    $discount = (float)test_input($_POST['discounted_price'] ?? 0);
    $accountType = test_input($_POST['account_type']);
	$phone = test_input($_POST['phone'] ?? '');
    $userReference = date('YmdHis');
    $fullname = ($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? '');
    
    // Using only validated fields from ac_process_electricity.php
    $product_description = "Purchase of " . test_input($_POST['providerName'] ?? 'Unknown Provider') . " Subscription for N$amount";
    $detail = "Electricity Payment";
    $type = "Electricity Subscription";
    $cust_discount = $amount - $discount;
    $token = ''; // Will be updated after payment
    $unit_purchased = ''; // Will be updated after payment
    $customer_name = ''; // Will be updated after validation
    $customer_address = ''; // Will be updated after validation
    $smartcard_number = $accountId; // Using accountId as smartcard_number

    // Check wallet balance
    $stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "User wallet not found";
        die(header('Location: ' . $_SERVER['HTTP_REFERER']));
    }

    $row = $result->fetch_assoc();
    $balance = $row['balance'];

    if ($balance < $discount) {
        $_SESSION['error'] = "Insufficient balance";
        die(header('Location: ' . $_SERVER['HTTP_REFERER']));
    }

    // Record transaction first with PENDING status
    $current_balance = $balance;
    $new_balance = $balance - $discount;
    $status = "PENDING";
    $profit = 0;

    $insert_sql = "INSERT INTO transactions (
        user_id, fullname, phone_number, 
        product_description, amount, status, type, 
        detail, transaction_ref, profit, cust_discount, 
        current_balance, new_balance, token, unit_purchased, 
        customer_name, customer_address, smartcard_number
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insert_sql);
    if ($stmt) {
        $stmt->bind_param(
            "isssdssssddddsssss", 
            $_SESSION['user_id'],    // user_id (i)
            $fullname,              // fullname (s)
            $phone,             // phone_number (s)
            $product_description,   // product_description (s)
            $discount,              // amount (d)
            $status,                // status (s)
            $type,                  // type (s)
            $detail,                // detail (s)
            $userReference,         // transaction_ref (s)
            $profit,                // profit (d)
            $cust_discount,         // cust_discount (d)
            $current_balance,       // current_balance (d)
            $new_balance,           // new_balance (d)
            $token,                 // token (s)
            $unit_purchased,        // unit_purchased (s)
            $customer_name,         // customer_name (s)
            $customer_address,      // customer_address (s)
            $smartcard_number       // smartcard_number (s)
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to record transaction: " . $stmt->error);
        }
        $transaction_id = $stmt->insert_id;
        $stmt->close();
    } else {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Deduct from wallet
    $update_stmt = $conn->prepare("UPDATE wallets SET balance = ? WHERE user_id = ?");
    $update_stmt->bind_param("di", $new_balance, $_SESSION['user_id']);
    if (!$update_stmt->execute()) {
        error_log("Balance update failed: " . $update_stmt->error);
    }
    $update_stmt->close();

    // Initialize API client and process payment
    $api = new ArtxApiClient(true, 'dpeaceapp.api.ngn', 'login@DPeaceAdmin1234');
    
    // Validate meter number and get customer info
    $products = $api->getOperatorProducts($operatorId, '8.1');
    $accountInfo = $api->lookupAccount($accountId, array_key_first($products['products']));
	// die(print_r($accountInfo));
    $customer_name = $accountInfo['customerName'] ?? '';
    $customer_address = $accountInfo['address'] ?? '';

    // Process payment
    $transaction = $api->executeTransaction([
        'operator' => $operatorId,
        'accountId' => $accountId,
        'amount' => $amount,
        'userReference' => $userReference,
        'extraParameters' => [
            'accountType' => $accountType
        ]
    ]);

	// print_r($transaction);
    // Array ( [id] => 202942381154621610 [operator] => Array ( [id] => 672 [name] => Nigeria Kaduna Elec. Prepaid [reference] => 17534607852564 ) [country] => Array ( [id] => NG [name] => Nigeria ) [amount] => Array ( [operator] => 500.00 [user] => 500.00 ) [currency] => Array ( [user] => NGN [operator] => NGN ) [productId] => 6094 [productType] => 3 [simulation] => [userReference] => ELEC20250725172622 [accountId] => 04280379696 [instructions] => Disco: KAEDC_PREPAID Amount: 500.00 Operator Ref: 17534607852564 Tax: 34.9 Bonus Unit: Bonus Token: Unit: 13.20 Token: 2901-5210-5624-2068-6289 [balance] => Array ( [initial] => 4998515.30 [transaction] => 500.00 [commission] => 5.10 [commissionPercentage] => 1.02 [final] => 4998020.40 [currency] => NGN ) ) 1

    $instructions = $transaction['instructions'] ?? '';

	// Extract the token using a regular expression
	if (preg_match('/Token: ([0-9]{4}-[0-9]{4}-[0-9]{4}-[0-9]{4}-[0-9]{4})/', $instructions, $matches)) {
    	$token = $matches[1];
	} else {
    	$token = null; // or handle the case where token isn't found
	}
	// var_dump($token); die;
    // Use regex to extract Unit and Token
    preg_match('/Unit:\s*([\d.]+)/', $instructions, $unit_matches);
	$unit_purchased = $unit_matches[1] ?? '';

    // Update transaction with electricity-specific data
    $status = "SUCCESS";
    
    $update_sql = "UPDATE transactions SET 
        status = ?,
        token = ?,
        unit_purchased = ?,
        customer_name = ?,
        customer_address = ?,
        new_balance = ?
        WHERE id = ?";
    
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssssdi", $status, $token, $unit_purchased, $customer_name, $customer_address, $new_balance, $transaction_id);
    if (!$stmt->execute()) {
        error_log("Failed to update transaction details: " . $stmt->error);
    }
    $stmt->close();

    // Redirect to receipt page
    header('Location: ../?page=receipt&id=' . $transaction_id);
    exit;

} catch (Exception $e) {
    // If any error occurs, refund the amount
    if (isset($transaction_id)) {
        $conn->query("UPDATE wallets SET balance='$balance' WHERE user_id=".$_SESSION['user_id']);
        $conn->query("UPDATE transactions SET status='FAILED', new_balance='$balance' WHERE id=$transaction_id");
    }

    if (strpos($e->getMessage(), 'Invalid destination') !== false) {
        $_SESSION['error'] = "Invalid meter number. Please check and try again.";
    } else {
        $_SESSION['error'] = $e->getMessage();
    }

    error_log("Electricity Payment Error: " . $e->getMessage());
    die(header('Location: ' . $_SERVER['HTTP_REFERER']));
}