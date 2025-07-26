<?php
// Enable error reporting for debugging
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
include "../conn.php";
include "../_/ac_config.php";

// Function to send standardized JSON responses
function jsonResponse($status, $message, $data = []) {
    if ($status === 'error') {
        echo "<script>alert('Error: $message');</script>";
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ]);
    }
    //exit;
}

// Validate POST request
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    jsonResponse('error', 'Invalid request method');
}

$ported_number = $_POST['ported_number'];

// Check required fields
$required_fields = ['biller_id', 'phone_number', 'bouquet_code', 'discounted_price', 'hidden_price'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field])) {
        jsonResponse('error', "Missing required field: $field");
    }
}

// Sanitize inputs
$biller_id = $_POST['biller_id'];
$phone_number = $_POST['phone_number'];
$bouquet_code = $_POST['bouquet_code'];
$amount = $_POST['hidden_price'];
$discount = $_POST['discounted_price'];
$fullname = isset($_SESSION['firstname'], $_SESSION['lastname']) ? 
    $_SESSION['firstname'] . ' ' . $_SESSION['lastname'] : '';
    
    // Phone number validation and provider determination
    $phone_prefix = substr($phone_number, 0, 4); // Extract the first 4 digits
	if ($ported_number == 'false') {
    if (preg_match('/^(0702|0704|0803|0806|0703|0706|0813|0816|0810|0814|0903|0906|0913)$/', $phone_prefix)) {
        if ($biller_id !== 'MTN-DATA') {
            echo "<script>alert('Invalid phone number. Please check and try again!');window.location.href='../?page=data';</script>";
            exit;
        }
    } elseif (preg_match('/^(0805|0807|0705|0815|0811|0905)$/', $phone_prefix)) {
        if ($biller_id !== 'GLO-DATA') {
            echo "<script>alert('Invalid phone number. Please check and try again!');window.location.href='../?page=data';</script>";
            exit;
        }
    } elseif (preg_match('/^(0802|0808|0708|0812|0701|0901|0902|0904|0907|0912)$/', $phone_prefix)) {
        if ($biller_id !== 'Airtel-DATA') {
            echo "<script>alert('Invalid phone number. Please check and try again!');window.location.href='../?page=data';</script>";
            exit;
        }
    } elseif (preg_match('/^(0809|0818|0817|0908|0909)$/', $phone_prefix)) {
    //    echo $provider;
        if ($biller_id !== '9mobile-DATA') {
            echo "<script>alert('Invalid phone number. Please check and try again!');window.location.href='../?page=data';</script>";
            exit;
        }
    } 
    }

// Check wallet balance using prepared statement
$stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    jsonResponse('error', 'User wallet not found');
}

$row = $result->fetch_assoc();
$balance = $row['balance'];

if ($balance < $discount) {
    jsonResponse('error', 'Insufficient balance');
}

// Generate unique request ID
$requestId = date('YmdHis');

/**
 * Validate network ID with provider API
 */
$validateEndpoint = VAS2NETS_BASE_URL . VAS2NETS_ENV_URI . "vtu/validate";
$validatePayload = [
    'customerId' => $phone_number,
    'requestId' => $requestId
];

$validateResponse = makeApiRequest($validateEndpoint, $validatePayload);

if (!$validateResponse['success']) {
    jsonResponse('error', 'Network validation request failed', [
        'error' => $validateResponse['error'],
        'http_code' => $validateResponse['httpCode']
    ]);
}

$validateData = json_decode($validateResponse['response'], true);

// Verify validation response structure
if (!isset($validateData['data']['status'])) {
    jsonResponse('error', 'Invalid validation response structure', [
        'response' => $validateData
    ]);
}

// Handle validation status (case-insensitive)
$validationStatus = strtolower($validateData['data']['status']);
if (!in_array($validationStatus, ['valid', 'success'])) {
    jsonResponse('error', $validateData['data']['message'] ?? 'Validation failed', [
        'status_received' => $validateData['data']['status']
    ]);
}

/**
 * Process payment after successful validation
 */
$paymentEndpoint = VAS2NETS_BASE_URL . VAS2NETS_ENV_URI . "vtu/payment";
$paymentPayload = [
    'billerId' => $biller_id,
    'customerId' => $phone_number,
    'bouquetCode' => $bouquet_code,
    'requestId' => $requestId,
    'amount' => $amount
];

$paymentResponse = makeApiRequest($paymentEndpoint, $paymentPayload);

if (!$paymentResponse['success']) {
    jsonResponse('error', 'Payment request failed', [
        'error' => $paymentResponse['error'],
        'http_code' => $paymentResponse['httpCode']
    ]);
}

$responseData = json_decode($paymentResponse['response'], true);

// Verify payment response structure
if (!isset($responseData['data']['status'])) {
    jsonResponse('error', 'Invalid payment response structure', [
        'response' => $responseData
    ]);
}

$status = strtolower($responseData['data']['status']);
$ref_id = $requestId;
$product_description = "Purchase of $biller_id";

// die(print_r($responseData));
// Array ( [status] => 200 [referenceId] => 1747908312889774342 [data] => Array ( [status] => Pending [message] => Please requery this transaction in few minutes time ) [description] => request has been processed )

// Update wallet if successful
if ($responseData['data']['status'] === 'Success') {
    $new_balance = $balance - $discount;
    $update_stmt = $conn->prepare("UPDATE wallets SET balance = ? WHERE user_id = ?");
    $update_stmt->bind_param("di", $new_balance, $_SESSION['user_id']);
    if (!$update_stmt->execute()) {
        error_log("Wallet update failed: " . $update_stmt->error);
    }
    $update_stmt->close();

    // Send email receipt
    $email = $_SESSION['email'] ?? '';
    if (!empty($email)) {
        $to = $email;
        $subject = 'DPeace App Receipt - Data Purchase';
        $message = '
        <html>
        <head>
            <title>DPeace AppReceipt - Data Purchase</title>
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
                 <h1>Data Purchase - Receipt</h1>
             </div>
            <div class="content">
                <p>Reference ID: '.$ref_id.'</p>
                <p>Transaction Details:</p>
                <ul>
                    <li>Name: '.$fullname.'</li>
                    <li>Description: '.$product_description.'</li>
                    <li>Phone Number: '.$phone_number.'</li>
                    <li>Amount: ₦'.number_format($discount, 2).'</li>
                    <li>Status: '.ucfirst($status).'</li>
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
} elseif ($responseData['data']['status'] == 'Pending') {
    $new_balance = $balance - $discount;
    $update_stmt = $conn->prepare("UPDATE wallets SET balance = ? WHERE user_id = ?");
    $update_stmt->bind_param("di", $new_balance, $_SESSION['user_id']);
    if (!$update_stmt->execute()) {
        error_log("Wallet update failed: " . $update_stmt->error);
    }
    $update_stmt->close();
    
    // Log pending transaction
    error_log("Transaction pending: Reference ID $ref_id, Status $status");
    jsonResponse('error', 'Transaction pending', [
        'reference_id' => $ref_id,
        'request_id' => $requestId,
        'status' => $status,
        'balance' => $balance
    ]);
} else {
    jsonResponse('error', 'Transaction failed', [
        'reference_id' => $ref_id,
        'request_id' => $requestId,
        'status' => $status,
        'balance' => $balance
    ]);
}

$typ = 'Data Purchase'; $prft = 0; $dtl = str_replace('-', ' ', $biller_id);
// Log transaction
$insert_stmt = $conn->prepare("
    INSERT INTO transactions 
    (user_id, fullname, phone_number, product_description, amount, status, transaction_ref, type, profit, detail) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$insert_stmt->bind_param(
    "isssssssss", 
    $_SESSION['user_id'], 
    $fullname, 
    $phone_number, 
    $product_description, 
    $discount, 
    $status, 
    $ref_id,
    $typ,
    $prft,
    $dtl
);

if (!$insert_stmt->execute()) {
    die($insert_stmt->error);
} else {
    $insert_id = $insert_stmt->insert_id;
    die(header('Location: ../?page=receipt&id=' . $insert_id));
}
$insert_stmt->close();

// Return success response
jsonResponse('success', 'Transaction processed', [
    'reference_id' => $ref_id,
    'status' => $status,
    'new_balance' => $new_balance ?? $balance
]);

/**
 * Helper function for API requests
 */
function makeApiRequest($url, $payload) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode(VAS2NETS_USERNAME . ':' . VAS2NETS_PASSWORD)
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FAILONERROR => true
    ]);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'success' => $httpCode == 200 && empty($error),
        'response' => $response,
        'error' => $error,
        'httpCode' => $httpCode
    ];
}