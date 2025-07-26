<?php
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

// Example usage:
try {
    // Query items for MTN biller in airtime scenario
    $response = PalmPayBillerService::queryItems('airtime', 'MTN');print_r($response);
    
    // Process successful response
    if (isset($response['data']) && is_array($response['data'])) {
        // echo "Available Items for {$response['data'][0]['billerId']}:\n";
        // foreach ($response['data'] as $item) {
        //     echo "- {$item['itemName']} (ID: {$item['itemId']})\n";
            
        //     if ($item['isFixAmount'] == 1) {
        //         echo "  Fixed Amount: {$item['amount']}\n";
        //     } else {
        //         echo "  Amount Range: {$item['minAmount']} - {$item['maxAmount']}\n";
        //     }
            
        //     echo "  Status: " . ($item['status'] == 1 ? 'Available' : 'Unavailable') . "\n";
            
        //     if (!empty($item['extInfo'])) {
        //         echo "  Additional Info:\n";
        //         if (isset($item['extInfo']['validityDate'])) {
        //             echo "    Validity: {$item['extInfo']['validityDate']} days\n";
        //         }
        //         if (isset($item['extInfo']['itemSize'])) {
        //             echo "    Size: {$item['extInfo']['itemSize']}\n";
        //         }
        //     }
        //     echo "\n";
        // }
    } else {
        // echo "No items found for this biller.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>