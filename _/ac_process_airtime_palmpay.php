<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

session_start();
include "../conn.php";
include "../_/ac_config.php";
// print_r($_POST);die;

class PalmPayConfig {
    const TEST_ENV_URL = 'https://open-gw-daily.palmpay-inc.com';
    const PROD_ENV_URL = 'https://open-gw-prod.palmpay-inc.com';
    
    public static $appId = 'L250530142620642487801';
    public static $merchantPrivateKeyFile = '../palmpay/private.pem'; // Path to private key file
    public static $palmPayPublicKeyFile = '../palmpay/public.pem';    // Path to public key file
    public static $countryCode = 'NG'; // Nigeria by default
    public static $isTestEnv = false;
    
    public static function getBaseUrl() {
        return self::$isTestEnv ? self::TEST_ENV_URL : self::PROD_ENV_URL;
    }
}

class PalmPaySignature {
    public static function generateSignature(array $params) {
        // Step 1: Sort and concatenate parameters
        $strA = self::buildSignString($params);
        
        // Step 2: Generate MD5 hash (uppercase)
        $md5Str = strtoupper(md5($strA));
        
        // Step 3: Sign with private key
        return self::rsaSign($md5Str);
    }
    
    private static function buildSignString(array $params) {
        // Remove empty values and trim spaces
        $filteredParams = array_filter($params, function($value) {
            return !empty($value) && trim($value) !== '';
        });
        
        // Sort by key in ASCII order
        ksort($filteredParams);
        
        // Build key=value pairs
        $parts = [];
        foreach ($filteredParams as $key => $value) {
            $parts[] = $key . '=' . trim($value);
        }
        
        return implode('&', $parts);
    }
    
    private static function rsaSign($data) {
        // Read private key from file
        $privateKey = file_get_contents(PalmPayConfig::$merchantPrivateKeyFile);
        if ($privateKey === false) {
            throw new Exception('Failed to read private key file');
        }
        
        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA1);
        return base64_encode($signature);
    }
    
    public static function verifyCallbackSignature($data, $signature) {
        // Read public key from file
        $publicKey = file_get_contents(PalmPayConfig::$palmPayPublicKeyFile);
        if ($publicKey === false) {
            throw new Exception('Failed to read public key file');
        }
        
        $signature = base64_decode($signature);
        return openssl_verify($data, $signature, $publicKey, OPENSSL_ALGO_SHA1) === 1;
    }
}

class PalmPayBillerService {
    /**
     * Query available items for a specific biller and scene code
     * 
     * @param string $sceneCode Business scenario code (e.g., 'airtime', 'data')
     * @param string $billerId Operator ID (e.g., 'NTN', 'WIN')
     * @return array API response containing item list
     * @throws Exception If the API request fails
     */
    public static function queryItems($sceneCode, $billerId) {
        // Prepare request data
        $requestData = [
            'requestTime' => round(microtime(true) * 1000), // Current timestamp in milliseconds
            'nonceStr' => self::generateNonceStr(), // Random string
            'version' => 'V2', // API version
            'sceneCode' => $sceneCode,
            'billerId' => $billerId
        ];
        
        // Generate signature
        $signature = PalmPaySignature::generateSignature($requestData);
        
        // Prepare headers
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . PalmPayConfig::$appId,
            'Signature: ' . $signature,
            'CountryCode: ' . PalmPayConfig::$countryCode,
        ];
        
        // Build API endpoint URL
        $url = PalmPayConfig::getBaseUrl() . '/api/v2/bill-payment/item/query';
        
        // Send request
        $response = self::sendRequest($url, $headers, json_encode($requestData));
        
        // Parse and return response
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse API response: ' . json_last_error_msg());
        }
        
        // Handle specific error codes
        if ($result['respCode'] !== '00000000') {
            self::handleItemErrors($result['respCode'], $result['respMsg']);
        }
        
        return $result;
    }
    
    /**
     * Handle specific item query errors
     */
    private static function handleItemErrors($code, $message) {
        switch ($code) {
            case 'SBPINVALID_PARAMETER':
                throw new Exception('Invalid parameters: ' . $message);
            case 'SBPINVALID_SCENE_CODE':
                throw new Exception('Invalid scene code: ' . $message);
            case 'SBPBILLER_DISABLE':
                throw new Exception('Biller unavailable: ' . $message);
            default:
                throw new Exception('API Error: ' . $message . ' (Code: ' . $code . ')');
        }
    }
    
    /**
     * Generate a random nonce string
     */
    private static function generateNonceStr($length = 32) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $str;
    }
    
    /**
     * Send HTTP request to PalmPay API
     */
    private static function sendRequest($url, $headers, $body) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Curl error: ' . $error);
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('API request failed with HTTP code: ' . $httpCode);
        }
        
        return $response;
    }
}

class PalmPayOrderService {
    /**
     * Create a new bill payment order
     * 
     * @param string $sceneCode Business scenario code (e.g., 'airtime', 'data')
     * @param string $outOrderNo Merchant order number (unique identifier)
     * @param int $amount Order amount in cents
     * @param string $notifyUrl Callback URL for payment notifications
     * @param string $billerId Operator ID
     * @param string $itemId Package ID
     * @param string $rechargeAccount Phone number to recharge
     * @param string|null $title Optional order title
     * @param string|null $description Optional order description
     * @param string|null $relationId Optional user-defined associated ID
     * @return array API response containing order details
     * @throws Exception If the API request fails
     */
    public static function createOrder(
        $sceneCode,
        $outOrderNo,
        $amount,
        $notifyUrl,
        $billerId,
        $itemId,
        $rechargeAccount,
        $title = null,
        $description = null,
        $relationId = null
    ) {
        // Validate required parameters
        $requiredParams = [
            'sceneCode' => $sceneCode,
            'outOrderNo' => $outOrderNo,
            'amount' => $amount,
            'notifyUrl' => $notifyUrl,
            'billerId' => $billerId,
            'itemId' => $itemId,
            'rechargeAccount' => $rechargeAccount
        ];
        
        foreach ($requiredParams as $param => $value) {
            if (empty($value)) {
                throw new Exception("Parameter '$param' is required");
            }
        }

        // Validate amount is numeric and positive
        if (!is_numeric($amount) || $amount <= 0) {
            throw new Exception("Amount must be a positive number");
        }

        // Prepare request data
        $requestData = [
            'requestTime' => round(microtime(true) * 1000), // Current timestamp in milliseconds
            'nonceStr' => self::generateNonceStr(), // Random string
            'version' => 'V2', // API version
            'sceneCode' => $sceneCode,
            'outOrderNo' => $outOrderNo,
            'amount' => $amount,
            'notifyUrl' => $notifyUrl,
            'billerId' => $billerId,
            'itemId' => $itemId,
            'rechargeAccount' => $rechargeAccount
        ];
        
        // Add optional parameters if provided
        if ($title !== null) $requestData['title'] = $title;
        if ($description !== null) $requestData['description'] = $description;
        if ($relationId !== null) $requestData['relationId'] = $relationId;
        
        // Generate signature
        $signature = PalmPaySignature::generateSignature($requestData);
        
        // Prepare headers
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . PalmPayConfig::$appId,
            'Signature: ' . $signature,
            'CountryCode: ' . PalmPayConfig::$countryCode,
        ];
        
        // Build API endpoint URL
        $url = PalmPayConfig::getBaseUrl() . '/api/v2/bill-payment/order/create';
        
        // Send request
        $response = self::sendRequest($url, $headers, json_encode($requestData));
        
        // Parse and return response
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse API response: ' . json_last_error_msg());
        }
        
        // Handle specific error codes
        if ($result['respCode'] !== '00000000' && $result['respCode'] !== '80808008') {
            self::handleOrderErrors($result['respCode'], $result['respMsg']);
        }
        
        return $result;
    }
    
    /**
     * Handle specific order creation errors
     */
    private static function handleOrderErrors($code, $message) {
        $errorMap = [
            'SBPDINVALID_PARAMETER' => 'Invalid parameters provided',
            'SBPDINVALID_SCENE_CODE' => 'Invalid business scenario code',
            'SBPDILLER_DISABLE' => 'Biller is currently unavailable',
            'SBPTTEM_DISABLE' => 'Item is currently unavailable',
            'SBPDILLER_TTEM_MISMATCH' => 'Biller and item do not match',
            'SBPAMOUNT_ABOVE_MAX_LIMIT' => 'Amount exceeds maximum limit',
            'SBPAMOUNT_BELOW_MIN_LIMIT' => 'Amount below minimum limit',
            'SBPAMOUNT_MISMATCH' => 'Amount does not match item requirements',
            'SBPMERCHANT_ERROR' => 'Merchant account error',
            'SBPMERCHANT_ACCOUNT_ERROR' => 'Merchant account configuration error',
            'SBPMERCHANT_ACCOUNT_STATUS_ERROR' => 'Merchant account status error',
            'SBPMERCHANT_ACCOUNT_BALANCE_NOT_ENOUGH' => 'Merchant account balance insufficient',
            'SBPTRADE_HAD_DEAL' => 'Duplicate order number detected'
        ];
        
        $errorMessage = $errorMap[$code] ?? 'API Error: ' . $message;
        throw new Exception($errorMessage . ' (Code: ' . $code . ')');
    }
    
    /**
     * Generate a random nonce string
     */
    private static function generateNonceStr($length = 32) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $str;
    }
    
    /**
     * Send HTTP request to PalmPay API
     */
    private static function sendRequest($url, $headers, $body) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Curl error: ' . $error);
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('API request failed with HTTP code: ' . $httpCode);
        }
        
        return $response;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token($_POST['csrf_token'] ?? '');
    // Process the form data
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

try {
    $discount = test_input($_POST['discounted_price'] ?? 0);
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

    // Phone number validation and provider determination
    $phone = test_input($_POST['phone_number'] ?? '');
    $ported_number = test_input($_POST['ported_number'] ?? 'false');
    $billerId = test_input($_POST['provider'] ?? 'MTN');
    
    // Validate phone number format first
    if (!preg_match('/^0[7-9][0-1]\d{8}$/', $phone)) {
        $_SESSION['error'] = "Invalid phone number format. Please enter a valid Nigerian phone number.";
        $stmt->close();
        die(header('Location: ' . $_SERVER['HTTP_REFERER']));
    }
    
    $phone_prefix = substr($phone, 0, 4);
    if ($ported_number == 'false') {
        if (preg_match('/^(0702|0704|0803|0806|0703|0706|0813|0816|0810|0814|0903|0906|0913)$/', $phone_prefix)) {
            if ($billerId !== 'MTN') {
                $_SESSION['error'] = "Invalid phone number for selected provider.";
                $stmt->close();
                die(header('Location: ' . $_SERVER['HTTP_REFERER']));
            }
        } elseif (preg_match('/^(0805|0807|0705|0815|0811|0905)$/', $phone_prefix)) {
            if ($billerId !== 'GLO') {
                $_SESSION['error'] = "Invalid phone number for selected provider.";
                $stmt->close();
                die(header('Location: ' . $_SERVER['HTTP_REFERER']));
            }
        } elseif (preg_match('/^(0802|0808|0708|0812|0701|0901|0902|0904|0907|0912)$/', $phone_prefix)) {
            if ($billerId !== 'AIRTEL') {
                $_SESSION['error'] = "Invalid phone number for selected provider.";
                $stmt->close();
                die(header('Location: ' . $_SERVER['HTTP_REFERER']));
            }
        } elseif (preg_match('/^(0809|0818|0817|0908|0909)$/', $phone_prefix)) {
            if ($billerId !== 'NINEMOBILE') {
                $_SESSION['error'] = "Invalid phone number for selected provider.";
                $stmt->close();
                die(header('Location: ' . $_SERVER['HTTP_REFERER']));
            }
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
    $outOrderNo = 'ORDER_' . uniqid();
    $amount = test_input($_POST['amount'] ?? 0);
    $billerId = test_input($_POST['provider'] ?? 'MTN');
    $phone = test_input($_POST['phone_number']) ?? '';
    $rechargeAccount = '0234' . test_input($_POST['phone_number'] ?? '');
    $description = "Purchase of $billerId-AIRTIME for $amount";
    $detail = "$billerId-AIRTIME";
    $relationId = $_SESSION['user_id'] ?? 'CUST' . uniqid();
    $fullname = ($_SESSION['firstname'] . ' ' . $_SESSION['lastname']) ?? 'Unknown User';
    $cust_discount = $amount - $discount;
    
    // First record the transaction as "PENDING"
    $status = "PENDING";
    $current_balance = $balance;
    $new_balance = $balance - $discount;
    
    // Insert the transaction record
    $sql = "INSERT INTO transactions (user_id, fullname, phone_number, transaction_ref, product_description, amount, status, profit, type, detail, cust_discount, current_balance, new_balance) VALUES
    ('$relationId', '$fullname', '$phone', '$outOrderNo', '$description', '$discount', '$status', '0', 'Airtime Purchase', '$detail', '$cust_discount', '$current_balance', '$new_balance')";
    
    if (!$conn->query($sql)) {
        $_SESSION['error'] = "Failed to record transaction: " . $conn->error;
        die(header('Location: ' . $_SERVER['HTTP_REFERER']));
    }
    
    $transaction_id = $conn->insert_id;
    
    // Update the wallet balance (deduct the amount)
    if (!$conn->query("UPDATE wallets SET balance='$new_balance' WHERE user_id=$relationId")) {
        $_SESSION['error'] = "Failed to update wallet balance: " . $conn->error;
        die(header('Location: ' . $_SERVER['HTTP_REFERER']));
    }
    
    // Now proceed with the PalmPay payment
    $item = PalmPayBillerService::queryItems('airtime', $billerId);
    $itemId = $item['data'][0]['itemId'] ?? null;
    
    $response = PalmPayOrderService::createOrder(
        'airtime',
        $outOrderNo,
        $amount * 100,
        'https://yourdomain.com/notify',
        $billerId,
        $itemId,
        $rechargeAccount,
        'Airtime Purchase',
        $description,
        $relationId
    );
    
    // Update transaction status based on PalmPay response
    if (isset($response['data']['orderNo'])) {
        $status = $response['data']['msg'];
        
        // If payment failed, refund the amount
        if ($status === 'FAIL') {
            $refund_balance = $balance; // Original balance
            $conn->query("UPDATE wallets SET balance='$refund_balance' WHERE user_id=$relationId");
            $new_balance = $balance; // Reset to original balance
        }
        
        // Update the transaction status
        $update_sql = "UPDATE transactions SET 
                        status = '$status',
                        new_balance = '$new_balance'
                        WHERE id = $transaction_id";
        
        if (!$conn->query($update_sql)) {
            // Log this error but don't show to user
            error_log("Failed to update transaction status: " . $conn->error);
        }
        
        if ($status !== 'FAIL') {
            die(header('Location: ../?page=receipt&id=' . $transaction_id));
        } else {
            $_SESSION['error'] = "Payment failed. Please try again.";
        }
    }
} catch (Exception $e) {
    // If any error occurs, refund the amount
    $conn->query("UPDATE wallets SET balance='$balance' WHERE user_id=$relationId");
    
    // Update transaction status to FAILED
    if (isset($transaction_id)) {
        $update_sql = "UPDATE transactions SET 
                        status = 'FAILED',
                        new_balance = '$balance'
                        WHERE id = $transaction_id";
        $conn->query($update_sql);
    }
    
    $_SESSION['error'] = "Payment processing error: " . $e->getMessage();
}

die(header('Location: ' . $_SERVER['HTTP_REFERER']));