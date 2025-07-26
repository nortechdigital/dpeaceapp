<?php
// Enable error reporting for debugging
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
include "../conn.php"; // contains $conn variable
include "../_/ac_config.php"; // contains API credentials

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$ported_number = $_POST['ported_number'];
    // Input validation
    $required_fields = ['provider', 'phone_number', 'amount', 'discounted_price'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo "<script>alert('Missing required field: $field');window.location.href='../?page=airtime';</script>";
            exit;
        }
    }

    $provider  = $_POST['provider'];
    $phone_number = $_POST['phone_number'];
    $amount = $_POST['amount'];
    $discount = $_POST['discounted_price'];
    $requestId = date('YmdHis');
    $billerId = $provider;
    $customerId = $phone_number;
    $fullname = $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];
    $email = $_SESSION['email'] ?? ''; // Use null coalescing operator

    // Phone number validation and provider determination
    $phone_prefix = substr($phone_number, 0, 4); // Extract the first 4 digits
	if ($ported_number == 'false') {
    if (preg_match('/^(0702|0704|0803|0806|0703|0706|0813|0816|0810|0814|0903|0906|0913)$/', $phone_prefix)) {
        if ($provider !== 'MTN-AIRTIME') {
            echo "<script>alert('Invalid phone number. Please check and try again!');window.location.href='../?page=airtime';</script>";
            exit;
        }
    } elseif (preg_match('/^(0805|0807|0705|0815|0811|0905)$/', $phone_prefix)) {
        if ($provider !== 'GLO-AIRTIME') {
            echo "<script>alert('Invalid phone number. Please check and try again!');window.location.href='../?page=airtime';</script>";
            exit;
        }
    } elseif (preg_match('/^(0802|0808|0708|0812|0701|0901|0902|0904|0907|0912)$/', $phone_prefix)) {
        if ($provider !== 'AIRTEL-AIRTIME') {
            echo "<script>alert('Invalid phone number. Please check and try again!');window.location.href='../?page=airtime';</script>";
            exit;
        }
    } elseif (preg_match('/^(0809|0818|0817|0908|0909)$/', $phone_prefix)) {
    //    echo $provider;
        if ($provider !== '9mobile-AIRTIME') {
            echo "<script>alert('Invalid phone number. Please check and try again!');window.location.href='../?page=airtime';</script>";
            exit;
        }
    }
    }

    // Check wallet balance
    $sql = "SELECT * FROM wallets WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('User not found!');window.location.href='../?page=airtime';</script>";
        exit;
    }

    $row = $result->fetch_assoc();
    $balance = $row['balance'];

    if ($balance < $discount) {
        echo "<script>alert('Insufficient balance. Please top up your account!');window.location.href='../?page=airtime';</script>";
        exit;
    }

    // API Request
    $endpoint = VAS2NETS_BASE_URL . VAS2NETS_ENV_URI . "vtu/payment";
    $payload = [
        'billerId' => $billerId,
        'customerId' => $customerId,
        'amount' => $amount,
        'requestId' => $requestId
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
        CURLOPT_FAILONERROR => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    if ($curlError) {
        error_log("cURL Error: " . $curlError);
        echo "<script>alert('Network error. Please try again!');window.location.href='../?page=airtime';</script>";
        exit;
    }

    $responseData = json_decode($response, true);
    curl_close($ch);

    if ($responseData === null) {
        echo "<script>alert('Invalid API response. Please try again!');window.location.href='../?page=airtime';</script>";
        exit;
    }

    // Process response
    if ($httpCode == 200) {
        $status = strtolower($responseData['data']['status'] ?? 'Success');
        $ref_id = $requestId;
        $product_description = "Purchase of $billerId";
        $type = $responseData['data']['type'] ?? 'Airtime Purchase';
        $detail = "$provider";
        // Update wallet if successful
         if ($status === 'success' || $status === 'pending') {
            $new_balance = $balance - $discount;
            $update_sql = "UPDATE wallets SET balance = ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("di", $new_balance, $_SESSION['user_id']);
            if (!$stmt->execute()) {
                error_log("Balance update failed: " . $stmt->error);
            }
        }

        // Log transaction
        $profit = 0; // Initialize profit to 0
        $insert_sql = "INSERT INTO transactions (user_id, fullname, phone_number, product_description, amount, status, type, detail, transaction_ref, profit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        if ($stmt) {
            $stmt->bind_param("isssdsssss", $_SESSION['user_id'], $fullname, $phone_number, $product_description, $discount, $status, $type, $detail, $ref_id, $profit);
            if (!$stmt->execute()) {
            error_log("Transaction logging failed: " . $stmt->error);
            } else {
                // Update transaction ID in the session
                $_SESSION['re']['transaction_id'] = $transaction_id = $stmt->insert_id;
            }
            $stmt->close();
        } else {
            error_log("Prepare failed: " . $conn->error);
        }

        // Set session data for receipt
        $_SESSION['re'] = [
            'status' => $status,
            'request_id' => $requestId,
            'fullname' => $fullname,
            'phone_number' => $phone_number,
            'product_description' => $product_description,
            'amount' => $discount,
            'created_at' => date('Y-m-d H:i:s'),
            'ref_id' => $ref_id
        ];

        // Send email receipt if successful
        if ($status === 'success' && !empty($email)) {
            $to = $email;
            $subject = 'DPeace App Receipt - Airtime Purchase';
            $message = '
            <html>
            <head>
                <title>DPeace App Receipt - Airtime Purchase</title>
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
                    <h1>Airtime Purchase - Receipt</h1>
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

        // Redirect to receipt page
        header('Location: ../?page=receipt&id=' . $transaction_id);
        exit;
    } else {
        $errorMsg = $responseData['error'] ?? 'Unknown error occurred';
        echo "<script>alert('Transaction failed: $errorMsg');window.location.href='../?page=airtime';</script>";
        exit;
    }
}