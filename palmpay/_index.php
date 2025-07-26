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

class PalmPayClient {
    public static function createOrder(array $orderData) {
        // Add common parameters
        $requestData = array_merge([
            'requestTime' => round(microtime(true) * 1000), // Milliseconds
            'version' => 'V1.1',
            'nonceStr' => self::generateNonceStr(),
        ], $orderData);
        
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
        
        // Send request
        $url = PalmPayConfig::getBaseUrl() . '/api/v2/payment/merchant/createorder';
        $response = self::sendRequest($url, $headers, json_encode($requestData));
        
        return json_decode($response, true);
    }
    
    private static function generateNonceStr($length = 32) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }
    
    private static function sendRequest($url, $headers, $body) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        curl_close($ch);
        
        return $response;
    }
}

class PalmPayCallback {
    public static function handle() {
        // Get raw POST data
        $rawData = file_get_contents('php://input');
        $data = json_decode($rawData, true);
        
        if (empty($data)) {
            http_response_code(400);
            exit('Invalid callback data');
        }
        
        // Extract signature from headers
        $headers = getallheaders();
        $signature = $headers['Signature'] ?? '';
        
        // Verify signature
        $isValid = PalmPaySignature::verifyCallbackSignature($rawData, $signature);
        
        if (!$isValid) {
            http_response_code(401);
            exit('Invalid signature');
        }
        
        // Process the callback
        $orderId = $data['orderId'];
        $status = $data['status']; // 0=unpaid, 1=paying, 2=success, 3=fail, 4=closed
        
        // Your business logic here
        self::updateOrderStatus($orderId, $status);
        
        // Must return 'success' (not JSON)
        echo 'success';
        exit;
    }
    
    private static function updateOrderStatus($orderId, $status) {
        // Implement your order status update logic
        // This is just a placeholder
    }
}

$orderData = [
    'orderId' => 'ORD' . time(),
    'amount' => 1000, // In smallest currency unit (e.g., kobo for NGN)
    'currency' => 'NGN',
    'notifyUrl' => 'https://yourdomain.com/palmpay/callback',
    'title' => 'Test Payment',
    'userId' => '12345',
    'country' => 'NG',
    'description' => 'Test payment description',
    'goodsDetails' => json_encode([[
        'goodsId' => 'PROD001',
        'goodsName' => 'Test Product',
        'quantity' => 1,
        'totalPrice' => 1000
    ]])
];

$response = PalmPayClient::createOrder($orderData);
print_r($response);