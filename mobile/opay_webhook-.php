<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('./conn.php'); // Database connection

function handleOpayWebhook($conn) {
    // Get headers in case-insensitive way
    $headers = array();
    foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))))] = $value;
        }
    }
    
    // Define required headers (case-insensitive)
    $requiredHeaders = [
        'Content-Type' => 'application/json',
        'X-Opay-Tranid' => null,
        'merchantid' => null
    ];
    
    // Validate headers
    $missingHeaders = array();
    foreach ($requiredHeaders as $header => $expectedValue) {
        $headerFound = false;
        foreach ($headers as $h => $v) {
            if (strtolower($h) == strtolower($header)) {
                if ($expectedValue !== null && $v != $expectedValue) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => "Invalid value for header: $header"]);
                    return;
                }
                $headerFound = true;
                break;
            }
        }
        if (!$headerFound) {
            $missingHeaders[] = $header;
        }
    }
    
    if (!empty($missingHeaders)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Missing required headers',
            'missing_headers' => $missingHeaders,
            'received_headers' => array_keys($headers)
        ]);
        return;
    }

    // Get and decode the request body
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload']);
        return;
    }

    // Validate required fields in body
    $requiredFields = [
        'status',
        'transactionId',
        'depositCode',
        'refId',
        'depositTime',
        'depositAmount',
        'currency',
        'errorCode',
        'errorMsg',
        'formatDateTime',
        'orderNo',
        'notes'
    ];

    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => "Missing required field: $field",
                'received_data' => array_keys($data)
            ]);
            return;
        }
    }

    // Extract data
    $transactionId = $data['transactionId'];
    $depositCode = $data['depositCode'];
    $status = $data['status'];
    $amount = $data['depositAmount'];
    $currency = $data['currency'];
    $orderNo = $data['orderNo'];
    $depositTime = $data['depositTime'];
    $errorMsg = $data['errorMsg'];
    $notes = $data['notes'];

    // Log the request for debugging
    file_put_contents('opay_webhook_log.txt', 
        date('Y-m-d H:i:s') . " - Transaction: $transactionId, Amount: $amount $currency, Status: $status\n" .
        "Full Data: " . json_encode($data) . "\n\n",
        FILE_APPEND
    );

    try {
        // Process successful payment
        if ($status === 'SUCCESS') {
            // Find user by deposit code
            $stmt = $conn->prepare("SELECT id, firstname, lastname, phone FROM users WHERE deposit_code = ?");
            $stmt->bind_param("s", $depositCode);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'User not found']);
                return;
            }

            $user = $result->fetch_assoc();
            $userId = $user['id'];
            $fullName = $user['firstname'] . ' ' . $user['lastname'];
            $phone = $user['phone'];

            // Calculate service charge and wallet balance
            if ($amount <= 7250) {
                $serviceCharge = 50;
                $walletBalance = $amount - $serviceCharge;
            } else {
                $percentageCharge = 0.007 * $amount;
                $serviceCharge = ($percentageCharge < 300) ? $percentageCharge : 300;
                $walletBalance = $amount - $serviceCharge;
            }

            // Update wallet balance
            $stmt = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
            $stmt->bind_param("di", $walletBalance, $userId);
            $stmt->execute();

            // Record transaction
            $productDescription = "Wallet funding of NGN $amount via OPay with service charge of NGN $serviceCharge. Wallet credited with NGN $walletBalance.";
            
            $stmt = $conn->prepare("INSERT INTO transactions (
                user_id, fullname, phone_number, product_description, 
                amount, status, type, detail, transaction_ref, profit
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param(
                "isssdssssi",
                $userId,
                $fullName,
                $phone,
                $productDescription,
                $walletBalance,
                $status,
                'Wallet topup',
                'Wallet Funding',
                $transactionId,
                $serviceCharge
            );
            $stmt->execute();

            // Success response
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Payment processed successfully',
                'transactionId' => $transactionId,
                'amount' => $amount,
                'walletBalance' => $walletBalance,
                'serviceCharge' => $serviceCharge
            ]);
        } else {
            // Handle failed payment
            http_response_code(200);
            echo json_encode([
                'status' => 'received',
                'message' => 'Payment notification received but status is not SUCCESS',
                'transactionStatus' => $status,
                'errorMsg' => $errorMsg
            ]);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Internal server error',
            'error' => $e->getMessage(),
            'trace' => $e->getTrace()
        ]);
    }
}

// Execute the webhook handler
handleOpayWebhook($conn);
?>