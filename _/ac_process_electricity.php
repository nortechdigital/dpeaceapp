<?php
// Enable error reporting
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';


include "../conn.php";
include "../_/ac_config.php";

// Session check
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'User session not found']);
    exit;
}

// Accept only POST requests
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Required POST fields
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

// Validate numeric input
if ($amount <= 0 || $discount <= 0) {
    echo "<script>alert('Invalid amount specified');window.location.href='../?page=electricity';</script>";
    exit;
}

// Generate request IDs
$validation_request_id = 'val_' . time() . '_' . bin2hex(random_bytes(4));
$payment_request_id = date('YmdHis');

// Step 1: VALIDATION REQUEST
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
}

// Step 2: CHECK WALLET
try {
    $sql = "SELECT balance FROM wallets WHERE user_id = ? FOR UPDATE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("User wallet not found");
    }

    $balance = (float)$result->fetch_assoc()['balance'];

    if ($balance < $discount) {
        throw new Exception("Insufficient balance. Please top up your account!");
    }

    // Step 3: PAYMENT REQUEST
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
        throw new Exception("Payment failed with HTTP code $payment_http_code");
    }

    $response_data = json_decode($payment_response, true);
    $status = $response_data['data']['status'] ?? '';
    // $smartcard_number = $response_data['data']['receiptInfo']['Meter Number'] ?? '';
	$smartcard_number = $customer_id;
    $customer_name = $response_data['data']['receiptInfo']['Name'] ?? $customer_name;
    $customer_address = $response_data['data']['receiptInfo']['Address'] ?? $customer_address;
    $unit_purchased = $response_data['data']['receiptInfo']['Unit Purchased'] ?? '';
    $token = $response_data['data']['token'] ?? '';
    $ref_id = $response_data['requestId'] ?? $payment_request_id;
    $product_description = "Purchase of $providerName Subscription for $amount";
    $type = 'Electricity Subscription';
    $profit = 0;
	$cust_discount = $amount - $discount;

    switch ($status) {
        case 'Success':
        case 'Pending':
            $conn->begin_transaction();

            // Deduct wallet
            $update_wallet = "UPDATE wallets SET balance = balance - ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_wallet);
            $stmt->bind_param("di", $discount, $_SESSION['user_id']);
            $stmt->execute();

            // Save transaction
            $t_status = strtolower($status);
            $current_balance = $balance;
            if ($status !== 'Failed'){
            	$new_balance = $balance - $discount;
        	}else{
            	$new_balance = $balance;
        	}
            $transaction_log = "INSERT INTO transactions (user_id, fullname, phone_number, amount, product_description, transaction_ref, detail, type, smartcard_number, customer_name, customer_address, unit_purchased, token, status, profit, cust_discount, current_balance, new_balance) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($transaction_log);
            $stmt->bind_param("issdssssssssssssss", $_SESSION['user_id'], $customer_name, $customer_phone, $discount, $product_description, $ref_id, $providerName, $type, $smartcard_number, $customer_name, $customer_address, $unit_purchased, $token, $t_status, $profit, $cust_discount, $current_balance, $new_balance);
            $stmt->execute();

            if ($status === 'Success') {
                sendReceiptEmail($email, $ref_id, $smartcard_number, $customer_name, $customer_address, $unit_purchased, $token);
                $conn->commit();
                echo "<script>alert('Transaction Successful!');window.location.href='../?page=receipt&id=" . $stmt->insert_id . "';</script>";
                exit;
            // } else {
            //     $conn->commit();
            //     handlePendingTransaction($conn, $payment_request_id, $discount, $_SESSION['user_id'], $email, $ref_id, $smartcard_number, $customer_name, $customer_address);
            //     exit;
            }

        case 'Failed':
        default:
            throw new Exception("Payment status: $status");
    }

} catch (Exception $e) {
    $conn->rollback();
    error_log("Electricity Payment Error: " . $e->getMessage()); $_SESSION['error'] = $e->getMessage();
    echo "<script>alert('Error processing transaction: " . addslashes($e->getMessage()) . "');window.location.href='../?page=electricity';</script>";
    exit;
}

// === SEND RECEIPT EMAIL FUNCTION ===
function sendReceiptEmail($email, $ref_id, $smartcard_number, $customer_name, $customer_address, $unit_purchased, $token) {
    if (empty($email)) return;

    $name_parts = explode(' ', trim($customer_name));
    $firstname = $name_parts[0] ?? '';
    $lastname = $name_parts[1] ?? '';

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.zoho.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'support@dpeaceapp.com'; // Replace with your Zoho email
        $mail->Password   = 'login@dpeaceSupport1';                 // Replace with your Zoho app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('support@dpeaceapp.com', 'DPeaceApp Support');
        $mail->addAddress($email, "$firstname $lastname");
        $mail->addReplyTo('support@dpeaceapp.com', 'DPeaceApp Support');

        $mail->isHTML(true);
        $mail->Subject = 'DPeace App Receipt - Electricity Payment';
        $mail->Body    = '
        <html>
        <head><style>
            body { font-family: Arial, sans-serif; }
            .header { color: #3366ff; text-align: center; }
            .content { margin: 20px 0; }
            .footer { font-size: 12px; color: #666; text-align: center; }
            .logo { display: block; margin: 0 auto; width: 150px; }
        </style></head>
        <body>
            <div class="header">
                <img src="https://dpeaceapp.com/img/dpeace-app.png" alt="DPeace Logo" class="logo">
                <h3>Electricity Payment Receipt</h3>
            </div>
            <div class="content">
                <p>Reference ID: ' . htmlspecialchars($ref_id) . '</p>
                <ul>
                    <li>Account/Metre Number: ' . htmlspecialchars($smartcard_number) . '</li>
                    <li>Customer Name: ' . htmlspecialchars($customer_name) . '</li>
                    <li>Customer Address: ' . htmlspecialchars($customer_address) . '</li>
                    <li>Unit Purchased: ' . htmlspecialchars($unit_purchased) . '</li>
                    <li>Token: ' . htmlspecialchars($token) . '</li>
                    <li>Amount: ' . htmlspecialchars($discount) . '</li>
                </ul>
            </div>
            <div class="footer">
                <p>© ' . date('Y') . ' DPeaceApp. All rights reserved.</p>
            </div>
        </body>
        </html>';

        $mail->send();
        error_log("Receipt email sent to $email");
    } catch (Exception $e) {
    	$_SESSION['error'] = $mail->ErrorInfo;
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
    }
}
die(header('location: ' . $_SERVER['HTTP_REFERER']));