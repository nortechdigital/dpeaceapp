<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

class PalmPayConfig {
    const TEST_ENV_URL = 'https://open-gw-daily.palmpay-inc.com';
    const PROD_ENV_URL = 'https://open-gw-prod.palmpay-inc.com';
    
    public static $appId = 'L250530142620642487801';
    public static $merchantPrivateKeyFile = 'private.pem'; // Path to private key file
    public static $palmPayPublicKeyFile = 'public.pem';    // Path to public key file
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
     * Query available billers for a specific scene code
     * 
     * @param string $sceneCode Business scenario code (e.g., 'airtime', 'data', 'betting')
     * @return array API response containing biller list
     * @throws Exception If the API request fails
     */
    public static function queryBillers($sceneCode) {
        // Prepare request data
        $requestData = [
            'requestTime' => round(microtime(true) * 1000), // Current timestamp in milliseconds
            'nonceStr' => self::generateNonceStr(), // Random string
            'version' => 'V2', // API version
            'sceneCode' => $sceneCode // Business scenario
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
        
        // Build API endpoint URL - ensure no double slashes
        $url = rtrim(PalmPayConfig::getBaseUrl(), '/') . '/api/v2/bill-payment/biller/query';
        
        // Send request
        $response = self::sendRequest($url, $headers, json_encode($requestData));
        
        // Parse and return response
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse API response: ' . json_last_error_msg());
        }
        
        return $result;
    }
    
    /**
     * Generate a random nonce string
     * 
     * @param int $length Length of the nonce string (default 32)
     * @return string Generated nonce string
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
     * 
     * @param string $url API endpoint URL
     * @param array $headers Request headers
     * @param string $body Request body (JSON encoded)
     * @return string API response
     * @throws Exception If the request fails
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

// Example usage:
try {
    // Query airtime billers
    $response = PalmPayBillerService::queryBillers('airtime');
    print_r($response);
    
    // Process response
    if ($response['respCode'] === '00000000') {
        // Success handling
    } else {
        // Error handling
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>