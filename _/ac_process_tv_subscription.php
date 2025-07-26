<?php
// Enable error reporting for debugging
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
require "../conn.php";
require "../_/ac_config.php";
// print_r($_POST);die;

class VAS2NetsTVService {
    private $baseUrl;
    private $envUri;
    private $username;
    private $password;
    
    public function __construct($baseUrl, $envUri, $username, $password) {
        $this->baseUrl = $baseUrl;
        $this->envUri = $envUri;
        $this->username = $username;
        $this->password = $password;
    }
    
    public function validateCustomer($billerId, $customerId, $requestId) {
        $endpoint = "{$this->baseUrl}{$this->envUri}tv/validate";
        
        $payload = [
            'billerId' => $billerId,
            'customerId' => $customerId,
            'requestId' => $requestId
        ];
        
        return $this->makeRequest($endpoint, $payload);
    }
    
    public function makePayment($billerId, $customerId, $requestId, $customerName, $amount, 
                               $bouquetCode = null, $customerNumber = null, $addonCode = null) {
        $endpoint = "{$this->baseUrl}{$this->envUri}tv/payment";
        
        $payload = [
            'billerId' => $billerId,
            'customerId' => $customerId,
            'requestId' => $requestId,
            'customerName' => $customerName,
            'amount' => $amount
        ];
        
        if ($bouquetCode !== null) {
            $payload['bouquetCode'] = $bouquetCode;
        }
        
        if ($customerNumber !== null) {
            $payload['customerNumber'] = $customerNumber;
        }
        
        if ($addonCode !== null) {
            $payload['addonCode'] = $addonCode;
        }
        
        return $this->makeRequest($endpoint, $payload);
    }
    
    public function getBouquets($billerId, $type = null) {
        $endpoint = "{$this->baseUrl}{$this->envUri}bouquet/tv/{$billerId}";
        
        if ($type !== null) {
            $endpoint .= "?type=" . urlencode($type);
        }
        
        return $this->makeRequest($endpoint, null, 'GET');
    }
    
    public function requeryTransaction($requestId) {
        $endpoint = "{$this->baseUrl}{$this->envUri}requery?requestId=" . urlencode($requestId);
        return $this->makeRequest($endpoint, null, 'GET');
    }
    
    private function makeRequest($url, $payload = null, $method = 'POST') {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($payload !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json'
                ]);
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        if ($decoded === null) {
            throw new Exception('Invalid JSON response');
        }
        
        return $decoded;
    }
}

// Initialize the service
$tvService = new VAS2NetsTVService(
    VAS2NETS_BASE_URL,           // BASE_URL - provided by VAS2Nets
    VAS2NETS_ENV_URI,            // ENV_URI - '/sandbox' for test, '/prod' for production
    VAS2NETS_USERNAME,           // API username
    VAS2NETS_PASSWORD            // API password
);


try {
    $biller_id = test_input($_POST['biller_id']) ?? NULL;
    $customer_id = test_input($_POST['smartcard_number']) ?? NULL;
    $request_id = date('YmdHis');
    $bundle_code = json_decode($_POST['bundle_code']);
	$bouquet_code = test_input($_POST['bouquet_code']);
	$discount = test_input($_POST['discounted_price']);
	$provider = test_input($_POST['provider']) ?? NULL;
	$phone_number = $_SESSION['phone'];
	$fullname = $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];
    $email = $_SESSION['email'] ?? '';
    $price = $code = $addon = NULL; $nm = [];
    foreach($bundle_code as $bc){
        $price += $bc->price;
    	$nm[] = $bc->name;
        if (!isset($code)) {
            $code = $bc->code;
        } else {
            $addon = $bc->code;
        }
    }
	$nm = implode(' + ', $nm);
    // Example 1: Validate a DSTV smartcard
    // $validation = $tvService->validateCustomer('DSTV', '7030935900', 'req12345');
    $validation = $tvService->validateCustomer($biller_id, $customer_id, $request_id);
    // print_r($validation);die;

    if ($validation['data']['status'] === 'Success') {
        
        // Check wallet balance
    $sql = "SELECT * FROM wallets WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('User not found!');window.location.href='../?page=cabletv';</script>";
        exit;
    }

    $row = $result->fetch_assoc();
    $balance = $row['balance'];

    if ($balance < $discount) {
        echo "<script>alert('Insufficient balance. Please top up your account!');window.location.href='../?page=cabletv';</script>";
        exit;
    }
        
        // Example 3: Subscribe to a DSTV bouquet
        $payment = $tvService->makePayment(
            $biller_id, 
            $customer_id, 
            $request_id,
            $validation['data']['customerName'],
            $price, // amount
            $code, // bouquet code
            $validation['data']['customerNumber'],
            $addon // optional addon code
        );
        // print_r($payment);
        
        // update wallet
    	$status = $validation['data']['status'];
    	$ref_id = $request_id;
        $product_description = "Purchase of $biller_id $nm";
        $type = "TV Subscription";
        $detail = "$provider";
    	$profit = "0";
    
        if ($status === 'Success' || $status === 'Pending') {
            $new_balance = $balance - $discount;
            $update_sql = "UPDATE wallets SET balance = ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("di", $new_balance, $_SESSION['user_id']);
            if (!$stmt->execute()) {
                die("Balance update failed: " . $stmt->error);
            }
        }

        // log transaction
        $insert_sql = "INSERT INTO transactions (user_id, fullname, phone_number, product_description, amount, status, type, detail, transaction_ref, profit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        if ($stmt) {
            $stmt->bind_param("isssdsssss", $_SESSION['user_id'], $validation['data']['customerName'], $phone_number, $product_description, $discount, $status, $type, $detail, $ref_id, $profit);
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

        // send email
    	if ($status === 'Success' && !empty($email)) {
            $to = $email;
            $subject = 'DPeace App Receipt - Cable TV Subscription';
            $message = '
            <html>
            <head>
                <title>DPeace App Receipt - Cable TV Subscription</title>
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
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}