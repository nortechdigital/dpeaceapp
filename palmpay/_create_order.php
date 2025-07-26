<?php
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
        $privateKey = "-----BEGIN PRIVATE KEY-----\n" . 
                     wordwrap(PalmPayConfig::$merchantPrivateKey, 64, "\n", true) . 
                     "\n-----END PRIVATE KEY-----";
        
        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA1);
        return base64_encode($signature);
    }
    
    public static function verifyCallbackSignature($data, $signature) {
        $publicKey = "-----BEGIN PUBLIC KEY-----\n" . 
                    wordwrap(PalmPayConfig::$palmPayPublicKey, 64, "\n", true) . 
                    "\n-----END PUBLIC KEY-----";
        
        $signature = base64_decode($signature);
        return openssl_verify($data, $signature, $publicKey, OPENSSL_ALGO_SHA1) === 1;
    }
}

class PalmPayConfig {
    const TEST_ENV_URL = 'https://open-gw-daily.palmpay-inc.com';
    const PROD_ENV_URL = 'https://open-gw-prod.palmpay-inc.com/';
    
    public static $appId = 'L231204055835021842101';
    public static $merchantPrivateKey = 'MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCgalkoceeiBxnXY9TszucuLxCeRWAHMg90VRc5NjY4ar/LJQDhpLk3Ty57u8nEf74ADUx29yTiefVxkhGgATBuAOt3e0PHpbPP6/yOj7/q+PxOR5J481y2ExGCLnJPbQZbWo6hQus33sjQp1EPqOlYWeylDVjpz2d87QWmzShDLYYHZV3x78AQg7hDvBOIpD1yUjbqz1aQJFyVhHHOLh/fa+WZlArRiJ2dBRkSp1YeHCFFYkpQ48kvQJ9bm0rH13s9+Mz0aOSb9uElJQcOOv13E0xQZlBKLBZREbjN6ChEjUELojCq2aCmwHKoruKiA1QnZMRsKjCyTzqIiC2jvvwnAgMBAAECggEADNuZW+GNZHJXQulwlq6a3xvMpDMBWHJNxNBRNTfALtN3ngvQP0XZxrIlEqvhp0tp6k0mlN6IaVLHNpwzp3SQ8jBGr2QE8cq5V/AdZTvmcSoV5xxbhDBVfQ6YN6wLY4xklwvyJMDdY7QKupa+q5epZHiIvE4Ok2cZb2z8J/uHv6KUjt6li/Nzoi055Rw2ogZe9cVDClG7i5L8WUSeFjE2IjbOI955IhIsJjcKTaIivkifVZ+vONqpOUYJEDKebHaXTHlzNFXyg3bIKPe5HPbir8VjtIe1Zq21bGkwV4QDmPpZ8WWXyDaiwNhOGo+VwZb/jWweGzgry5s0V1g9Q90amQKBgQDu94FA03e3Yh/LkLmaBFHXsErOD4wSBV29QPRyUmbhyOa3ADNOoaR2YRRRlKmS+zOKJHDyxuR6IPYD6up1sYKF2T0mMKoUhqcp6+j/iPw1rbzTSbwJwJOwCE0CdEQPyTjDtDAL4egKYpShYXmO2Qlwk42FtFquG6XfVOGqPv2WMwKBgQCr2X5vKHWeEbeY9S9rhMKh/H++QkwxU2/Urob3A1eHN5CeBWQx82W59BjpLuxYiNemReHaHr1FNvjEK6Zr9sBhSZmzDlmNAVDnVhc7MZ4fCYRO7HmLejCjx+K+mcH2zpH+8A4JBBYjntBMJKvIyXm2IwmcOOTB56hV3Oycof8GPQKBgB5MCPokFXiNm0Re2/k39PxooINRm0upnIHjG1rnMZ4Mr5uiDd85RTWxBzd0pq845AbuqddN+ie1yBslDIbRc5/us/8EinvBuq3o+Ah14KwZk+gh4BJIdTELTGA0R3DM7UJ6tOC8yoOOjhOL3TKMN9MrEfVSsXCDltsi0t2X0OTbAoGAcF2SElS+M1EaX2VSUFdKfGiBjoIDF+2andJQZYtF3CA0615TGWYxCdnVwALyfyFbAFmJR/n5gBxlpL913fpF6FcbrLyhSVWm9NyR7B6RaXHrlT+CafTHgQ/d7wrSjPKc+7kzNCn73+akBGWl/W/fqXxXeFKrIS68HwiJnhE+k3ECgYAaGCeGhz7geoZONXkPpDVoOAmjlwHrUM5LOX0PsGUQbz40TDkm1V2daA/9hUb3XS+7FK8CmhKdjPliNNPd6oJh8343d7eUGJ4P8OG3C0RWq7kjc+BeL3vfNBILcQSM17aqDtshYqRBfE+W7snD9boRuSXOtxUZdKIo9qTkNLaAqA==';
    public static $palmPayPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAoGpZKHHnogcZ12PU7M7nLi8QnkVgBzIPdFUXOTY2OGq/yyUA4aS5N08ue7vJxH++AA1Mdvck4nn1cZIRoAEwbgDrd3tDx6Wzz+v8jo+/6vj8TkeSePNcthMRgi5yT20GW1qOoULrN97I0KdRD6jpWFnspQ1Y6c9nfO0Fps0oQy2GB2Vd8e/AEIO4Q7wTiKQ9clI26s9WkCRclYRxzi4f32vlmZQK0YidnQUZEqdWHhwhRWJKUOPJL0CfW5tKx9d7PfjM9Gjkm/bhJSUHDjr9dxNMUGZQSiwWURG4zegoRI1BC6IwqtmgpsByqK7iogNUJ2TEbCowsk86iIgto778JwIDAQAB';
    public static $countryCode = 'NG'; // Nigeria by default
    public static $isTestEnv = true;
    
    public static function getBaseUrl() {
        return self::$isTestEnv ? self::TEST_ENV_URL : self::PROD_ENV_URL;
    }
}

// Example usage:
try {
    // Create an airtime order
    $response = PalmPayOrderService::createOrder(
        'airtime',                      // sceneCode
        'ORDER_' . uniqid(),            // outOrderNo (unique merchant order number)
        10000,                         // amount (100.00 NGN in cents)
        'https://yourdomain.com/notify',// notifyUrl
        'MTN',                         // billerId
        '5267001812690',                // itemId
        '023408065615684',                  // rechargeAccount
        'Airtime Purchase',             // title (optional)
        'MTN 100 Naira airtime',        // description (optional)
        'CUST12345'                     // relationId (optional)
    );
    
    print_r($response);
    
    // Process successful response
    if (isset($response['data']['orderNo'])) {
        echo "Order created successfully. PalmPay Order No: " . $response['data']['orderNo'];
        echo "Status: " . ($response['data']['orderStatus'] == 1 ? 'SUCCESS' : 'PENDING');
    }
} catch (Exception $e) {
    echo "Error creating order: " . $e->getMessage();
}
?>