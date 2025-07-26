<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "../conn.php";
include "../_/ac_config.php";

// Validate session first
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'User session not found']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Validate required fields
$required = ['biller_id', 'phone', 'account_value', 'amount', 'discounted_price'];
$missing = array_diff($required, array_keys($_POST));
if (!empty($missing)) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields: ' . implode(', ', $missing)]);
    exit;
}

// Sanitize inputs
$biller_id = trim($conn->real_escape_string($_POST['biller_id']));
$customer_id = trim($conn->real_escape_string($_POST['account_value']));
$email = trim($conn->real_escape_string($_SESSION['email']));
$amount = (float)$_POST['amount'];
$discount = (float)$_POST['discounted_price'];
$customer_name = trim($conn->real_escape_string($_POST['customer_name'] ?? ''));
$customer_address = trim($conn->real_escape_string($_POST['customer_address'] ?? ''));
$customer_phone = trim($conn->real_escape_string($_POST['phone'] ?? ''));
$providerName = trim($conn->real_escape_string($_POST['providerName'] ?? ''));

// Validate numeric values
if ($amount <= 0 || $discount <= 0) {
    echo "<script>alert('Invalid amount specified');window.location.href='../?page=electricity';</script>";
    exit;
}

// Generate request IDs
$validation_request_id = 'val_' . time() . '_' . bin2hex(random_bytes(4));
// $payment_request_id = 'pay_' . time() . '_' . bin2hex(random_bytes(4));
$payment_request_id = date('YmdHis');

// 1. VALIDATION REQUEST
$validation_endpoint = VAS2NETS_BASE_URL . VAS2NETS_ENV_URI . "disco/validate";
$validation_payload = [
    'billerId' => $biller_id,
    'customerId' => $customer_id,
    'requestId' => $validation_request_id
];

$ch = curl_init($validation_endpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Basic " . base64_encode(VAS2NETS_USERNAME . ":" . VAS2NETS_PASSWORD)
    ],
    CURLOPT_POSTFIELDS => json_encode($validation_payload),
    CURLOPT_TIMEOUT => 30
]);

$validation_response = curl_exec($ch);
$validation_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($validation_http_code != 200) {
    echo "<script>alert('Validation Failed. Please check your account details and try again!');window.location.href='../?page=electricity';</script>";
    exit;
} else { 
// var_dump($validation_response); 
// string(201) "{"status":200,"data":{"status":"Success","message":"Validation was successful","customerName":"ENGR. ABENI","customerAddress":"NYSC AREA 5 Und St. Garki 80"},"description":"request has been processed"}"
}

// 2. CHECK WALLET BALANCE
// $conn->begin_transaction();

try {
    $sql = "SELECT balance FROM wallets WHERE user_id = ? FOR UPDATE";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare wallet query: " . $conn->error);
    }
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("User wallet not found");
    }

    $row = $result->fetch_assoc();
    $balance = (float)$row['balance'];

    if ($balance < $discount) {
        throw new Exception("Insufficient balance. Please top up your account!");
    }

    // 3. PAYMENT REQUEST
    $payment_endpoint = VAS2NETS_BASE_URL . VAS2NETS_ENV_URI . "disco/payment";
    $payment_payload = [
        'billerId' => $biller_id,
        'customerId' => $customer_id,
        'requestId' => $payment_request_id,
        'amount' => $amount,
        'customerName' => $customer_name,
        'customerAddress' => $customer_address
    ];

    $ch = curl_init($payment_endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Basic " . base64_encode(VAS2NETS_USERNAME . ":" . VAS2NETS_PASSWORD)
        ],
        CURLOPT_POSTFIELDS => json_encode($payment_payload),
        CURLOPT_TIMEOUT => 30
    ]); 

    $payment_response = curl_exec($ch);
    $payment_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($payment_http_code != 200) {
        throw new Exception("Payment request failed with HTTP code $payment_http_code");
    }

    $response_data = json_decode($payment_response, true);

    $status = $response_data['data']['status'] ?? '';
    $smartcard_number = $response_data['data']['receiptInfo']['Smartcard Number'] ?? '';
	$smartcard_number = $response_data['data']['receiptInfo']['Meter Number'] ?? $smartcard_number;
    $customer_name = $response_data['data']['receiptInfo']['Name'] ?? '';
    $customer_address = $response_data['data']['receiptInfo']['Address'] ?? '';
    $unit_purchased = $response_data['data']['receiptInfo']['Unit Purchased'] ?? '';
    $token = $response_data['data']['token'] ?? '';
    $ref_id = $response_data['requestId'] ?? $payment_request_id;
    $product_description = "Purchase of $providerName Subscription";
    $detail = "$providerName";
	$profit = 0;

    // Handle different statuses
    switch ($status) {
        case 'Success':
            // Deduct from wallet (only the discounted amount)
            $update_wallet = "UPDATE wallets SET balance = balance - ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_wallet);
            $stmt->bind_param("di", $discount, $_SESSION['user_id']);
            $stmt->execute();
            
            // Log successful transaction
            $type = 'Electricity Subscription';
            $t_status = 'success';
            $transaction_log = "INSERT INTO transactions (user_id, fullname, phone_number, amount, product_description, transaction_ref, detail, type, smartcard_number, customer_name, customer_address, unit_purchased, token, status, profit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($transaction_log);
            $stmt->bind_param("issdsssssssssss", $_SESSION['user_id'], $customer_name, $customer_phone, $amount, $product_description, $ref_id, $detail, $type, $smartcard_number, $customer_name, $customer_address, $unit_purchased, $token, $t_status, $profit);
            $stmt->execute();
            
            // Send receipt email
            sendReceiptEmail($email, $ref_id, $smartcard_number, $customer_name, $customer_address, $unit_purchased, $token);
            
            $conn->commit();
    		// echo "<script>alert('Transaction Successful!');</script>";
            echo "<script>alert('Transaction Successful!');window.location.href='../?page=receipt&id=" . $stmt->insert_id ."';</script>";
            exit;
        case 'Pending':
            // Deduct from wallet first (only the discounted amount)
            $update_wallet = "UPDATE wallets SET balance = balance - ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_wallet);
            $stmt->bind_param("di", $discount, $_SESSION['user_id']);
            $stmt->execute();
            
            // Log pending transaction
            $type = 'Electricity Subscription';
            $t_status = 'pending';
            $transaction_log = "INSERT INTO transactions (user_id, fullname, phone_number, amount, product_description, transaction_ref, detail, type, smartcard_number, customer_name, customer_address, unit_purchased, token, status, profit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($transaction_log);
            $stmt->bind_param("issdsssssssssss", $_SESSION['user_id'], $customer_name, $customer_phone, $amount, $product_description, $ref_id, $detail, $type, $smartcard_number, $customer_name, $customer_address, $unit_purchased, $token, $t_status, $profit);
            $stmt->execute();
            
            $conn->commit();
            
            // Requery logic
            handlePendingTransaction($conn, $payment_request_id, $discount, $_SESSION['user_id'], $email, $ref_id, $smartcard_number, $customer_name, $customer_address);
            exit;
            
        case 'Failed':
            // Log failed transaction without deducting from wallet
            $type = 'Electricity Subscription';
            $t_status = 'failed';
            $transaction_log = "INSERT INTO transactions (user_id, fullname, phone_number, amount, product_description, transaction_ref, detail, type, smartcard_number, customer_name, customer_address, unit_purchased, token, status, profit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($transaction_log);
            $stmt->bind_param("issdssssssssss", $_SESSION['user_id'], $customer_name, $customer_phone, $amount, $product_description, $ref_id, $detail, $type, $smartcard_number, $customer_name, $customer_address, $unit_purchased, $token, $t_status, $profit);
            $stmt->execute();
            
            $conn->commit();
            echo "<script>alert('Transaction Failed. Please try again!');window.location.href='../?page=electricity';</script>";
            exit;
            
        default:
            throw new Exception("Unknown payment status: $status");
    }
} catch (Exception $e) {
    $conn->rollback();
    error_log("Electricity Payment Error: " . $e->getMessage());
    echo "<script>alert('Error processing transaction: " . addslashes($e->getMessage()) . "');window.location.href='../?page=electricity';</script>";
    exit;
}

function sendReceiptEmail($email, $ref_id, $smartcard_number, $customer_name, $customer_address, $unit_purchased, $token) {
    if (empty($email)) return;
    
    $to = $email;
    $subject = 'DPeace App Receipt - Electricity Payment';
    $message = '
    <html>
    <head>
        <title>DPeace App Receipt - Electricity Payment</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { color: #3366ff; text-align: center; }
            .content { margin: 20px 0; }
            .footer { font-size: 12px; color: #666; text-align: center; }
            .logo { display: block; margin: 0 auto; width: 150px; }
        </style>
    </head>
    <body>
        <div class="header">
            <img src="https://dpeaceapp.com/img/dpeace-app.png" alt="DPeace Logo" class="logo">
            <h1>Electricity Payment - Receipt</h1>
        </div>
        <div class="content">
            <p>Reference ID: '.htmlspecialchars($ref_id).'</p>
            <p>Transaction Details:</p>
            <ul>
                <li>Account/Metre Number: '.htmlspecialchars($smartcard_number).'</li>
                <li>Customer Name: '.htmlspecialchars($customer_name).'</li>
                <li>Customer Address: '.htmlspecialchars($customer_address).'</li>
                <li>Unit Purchased: '.htmlspecialchars($unit_purchased).'</li>
                <li>Token: '.htmlspecialchars($token).'</li>
            </ul>
        </div>
        <div class="footer">
            <p>© '.date('Y').' DPeaceApp. All rights reserved.</p>
        </div>
    </body>
    </html>';

    $headers = [
        'From: no-reply@dpeaceapp.com',
        'Reply-To: no-reply@dpeaceapp.com',
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'X-Mailer: PHP/' . phpversion()
    ];

    mail($to, $subject, $message, implode("\r\n", $headers));
}

function handlePendingTransaction($conn, $payment_request_id, $discount, $user_id, $email, $ref_id, $smartcard_number, $customer_name, $customer_address) {
    $max_attempts = 5;
    $attempt = 0;
    $resolved = false;

    while ($attempt < $max_attempts && !$resolved) {
        sleep(30); // wait 30 seconds before each requery
        
        $requery_endpoint = VAS2NETS_BASE_URL . VAS2NETS_ENV_URI . "disco/requery?requestId=$payment_request_id";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $requery_endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Basic " . base64_encode(VAS2NETS_USERNAME . ":" . VAS2NETS_PASSWORD)
            ],
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $requery_data = json_decode($response, true);
            $new_status = $requery_data['data']['status'] ?? 'unknown';
            
            $conn->begin_transaction();
            try {
                if ($new_status === 'Success') {
                    $token = $requery_data['data']['token'] ?? '';
                    $unit_purchased = $requery_data['data']['unitPurchased'] ?? '';
                    $smartcard_number = $requery_data['data']['smartcardNumber'] ?? '';
                    
                    // Update transaction record
                    $update_transaction = "UPDATE transactions SET status = 'success', token = ?, unit_purchased = ? WHERE transaction_ref = ?";
                    $stmt = $conn->prepare($update_transaction);
                    $stmt->bind_param("sss", $token, $unit_purchased, $payment_request_id);
                    $stmt->execute();
                    
                    sendReceiptEmail($email, $ref_id, $smartcard_number, $customer_name, $customer_address, $unit_purchased, $token);
                    
                    $conn->commit();
                    $resolved = true;
                    
                    // Redirect to success page
                    echo "<script>alert('Transaction Successful!');window.location.href='../?page=receipt&ref_id=$ref_id';</script>";
                    exit;
                    
                } elseif ($new_status === 'Failed') {
                    // Rollback wallet
                    $rollback_wallet = "UPDATE wallets SET balance = balance + ? WHERE user_id = ?";
                    $stmt = $conn->prepare($rollback_wallet);
                    $stmt->bind_param("di", $discount, $user_id);
                    $stmt->execute();
                    
                    // Update transaction as failed
                    $update_transaction = "UPDATE transactions SET status = 'failed' WHERE transaction_ref = ?";
                    $stmt = $conn->prepare($update_transaction);
                    $stmt->bind_param("s", $payment_request_id);
                    $stmt->execute();
                    
                    $conn->commit();
                    $resolved = true;
                    
                    echo "<script>alert('Transaction failed after requery. Your wallet has been refunded.');window.location.href='../?page=electricity';</script>";
                    exit;
                }
            } catch (Exception $e) {
                $conn->rollback();
                error_log("Requery Error: " . $e->getMessage());
            }
        }
        
        $attempt++;
    }

    if (!$resolved) {
        echo "<script>alert('Transaction still pending. Please check your transaction history later.');window.location.href='../?page=transaction_history';</script>";
    }
}