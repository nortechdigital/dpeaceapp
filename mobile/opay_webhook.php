<?php

require_once('./conn.php'); // Assumes $conn is defined here

function handleOpayWebhook($conn) {
    $body = file_get_contents('php://input');
    file_put_contents('opay_raw_webhook.log', $body . PHP_EOL, FILE_APPEND);

    $data = json_decode($body, true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON payload']);
        return;
    }

    // Extract with fallbacks
    $status = $data['status'] ?? null;
    $transactionId = $data['transactionId'] ?? $data['tranId'] ?? null;
    $depositCode = $data['depositCode'] ?? $data['code'] ?? null;
    $refId = $data['refId'] ?? $data['reference'] ?? null;
    $depositTime = $data['depositTime'] ?? $data['settlementTime'] ?? null;
    $depositAmount = $data['depositAmount'] ?? $data['amount'] ?? null;
    $currency = $data['currency'] ?? 'NGN';
    $errorCode = $data['errorCode'] ?? $data['code'] ?? null;
    $errorMsg = $data['errorMsg'] ?? $data['message'] ?? '';
    $formatDateTime = $data['formatDateTime'] ?? date('c');
    $orderNo = $data['orderNo'] ?? $data['orderId'] ?? null;
    $notes = $data['notes'] ?? $data['description'] ?? '';
    $amount = $depositAmount;

    // Only validate truly essential fields
    if (!$status || !$transactionId || !$depositCode) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing core fields: status, transactionId, or depositCode']);
        return;
    }

    // Calculate wallet balance and service charge
    $wallet_balance = 0;
    $service_charge = 0;

    if ($amount <= 7250) {
        $service_charge = 50;
        $wallet_balance = $amount - $service_charge;
    } else {
        $percentage_charge = 0.007 * $amount;
        $service_charge = ($percentage_charge < 300) ? $percentage_charge : 300;
        $wallet_balance = $amount - $service_charge;
    }

    // Validate all required fields in the payload
    $requiredFields = [
        'status', 'transactionId', 'depositCode', 'refId',
        'depositTime', 'depositAmount', 'currency', 'errorCode',
        'errorMsg', 'formatDateTime', 'orderNo', 'notes'
    ];

    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing field: $field"]);
            return;
        }
    }

    // Log request body for debugging/auditing
    file_put_contents('opay_webhook_log.txt', json_encode($data) . PHP_EOL, FILE_APPEND);

    try {
        // Find user by depositCode
        $stmt = $conn->prepare("SELECT * FROM users WHERE deposit_code = ?");
        $stmt->bind_param("s", $depositCode);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['error' => "User not found with depositCode: $depositCode"]);
            return;
        }

        $user = $result->fetch_assoc();
        if (!$user || !isset($user['id'], $user['firstname'], $user['lastname'], $user['phone'])) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch user details']);
            return;
        }

        $userId = $user['id'];
        $fullname = $user['firstname'] . ' ' . $user['lastname'];
        $phoneNumber = $user['phone'];
        $type = 'Wallet topup';
        $details = 'Wallet Funding';
        $product_description = "Wallet funding of N$amount via Opay Digital Wallet with service charge of N$service_charge was successful. Wallet credited with N$wallet_balance.";

        // Check wallet balance
        $stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Wallet not found for user']);
            return;
        }

        $row = $result->fetch_assoc();
        $balance = $row['balance'];

        if ($status === 'SUCCESS') {
            // Update wallet balance
            $stmt = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
            $stmt->bind_param("di", $wallet_balance, $userId);
            $stmt->execute();

            // Insert transaction record
            $profit = 0; // Initialize profit to 0
            $insert_sql = "INSERT INTO transactions (user_id, fullname, phone_number, product_description, amount, status, type, detail, transaction_ref, profit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("isssdssssi", $userId, $fullname, $phoneNumber, $product_description, $wallet_balance, $status, $type, $details, $transactionId, $profit);
            $stmt->execute();
        }

        // Respond with success
        http_response_code(200);
        echo json_encode([
            'message' => 'Webhook processed successfully',
            'userId' => $userId,
            'depositCode' => $depositCode
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
    }
}

// Execute the webhook handler
handleOpayWebhook($conn);