<?php
date_default_timezone_set('Africa/Lagos');
require 'vendor/autoload.php';
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

class OPayDigitalWallet {
    private $opayPublicKey;
    private $merchantPrivateKey;
    private $clientAuthKey;
    private $baseUrl = 'https://payapi.opayweb.com';
    
    public function __construct($opayPublicKey, $merchantPrivateKey, $clientAuthKey) {
        $this->opayPublicKey = PublicKeyLoader::load($opayPublicKey)
            ->withHash('sha256')
            ->withPadding(RSA::SIGNATURE_PKCS1);
            
        $this->merchantPrivateKey = PublicKeyLoader::load($merchantPrivateKey)
            ->withHash('sha256')
            ->withPadding(RSA::SIGNATURE_PKCS1);
            
        $this->clientAuthKey = $clientAuthKey;
    }
    
    public function createDigitalWallet($walletData) {
        $endpoint = '/api/v2/third/depositcode/generateStaticDepositCode';
        $url = $this->baseUrl . $endpoint;
        
        // Prepare request body
        $requestBody = json_encode([
            'opayMerchantId' => $walletData['opayMerchantId'],
            'name' => $walletData['name'],
            'refid' => $walletData['refid'] ?? '',
            'email' => $walletData['email'] ?? '',
            'phone' => $walletData['phone'] ?? '',
            'accountType' => $walletData['accountType'],
            'sendPassWordFlag' => $walletData['sendPassWordFlag'] ?? 'N'
        ]);
        
        // Get current timestamp
        $timestamp = round(microtime(true) * 1000);
        
        // Encrypt the request body (simplified for example)
        $paramContent = $this->encryptData($requestBody);
        
        // Create signature
        $signature = $this->createSignature($requestBody, $timestamp);
        
        // Prepare final request payload
        $payload = json_encode([
            'paramContent' => $paramContent,
            'sign' => $signature
        ]);
        
        // Prepare request headers
        $headers = [
            'Content-Type: application/json',
            'clientAuthKey: ' . $this->clientAuthKey,
            'version: V1.0.1',
            'bodyFormat: JSON',
            'timestamp: ' . $timestamp
        ];
        
        // Make the API request
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        return $this->handleResponse($response);
    }
    
    private function encryptData($data) {
        // In a real implementation, you would encrypt with OPay's public key
        // For this example, we're just base64 encoding
        return base64_encode($data);
    }
    
    private function createSignature($data, $timestamp) {
        // Combine data with timestamp
        $dataToSign = $data . $timestamp;
        
        // Create signature
        $signature = $this->merchantPrivateKey->sign($dataToSign);
        
        return base64_encode($signature);
    }
    
    private function handleResponse($response) {
        $responseData = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response: ' . $response);
        }
        
        // In a real implementation, you would verify the signature
        // and decrypt the response data here
        
        return $responseData;
    }
}

// Example usage:
try {
    // Configuration - replace with your actual keys
    $opayPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCEK5JGJLqz61zM2FHDG3BzdHWaLKZXooVm5ZuKlRbSuJxsWpkwYInc76w9Y/eeyef780s7tCDR8euDjlszVnq9dWe0BkdoY3FJNz9fplWH79QJZwkZ1t56ZeRvAx9w+39i1Q29LdfWQT/xePc5Ee8rX+xPXLsHyfn3AF8dV5FdJwIDAQAB';

    $merchantPrivateKey = <<<EOD
-----BEGIN PRIVATE KEY-----
MIICdQIBADANBgkqhkiG9w0BAQEFAASCAl8wggJbAgEAAoGBAL97lgMOYQS8VJqj
fbDT6D56VqR9W/tedY9whn1HBod02kr32tR98rh0JQB71yAENVZG4dHBMoVGV2Hq
pmLpANH3HAYGCzf8gmOdFnbV49OxGSxhQi4My99fyoRLFKRC3grtrtLKxCa428hY
3dJASutwHDuOjEbW/beOJMo+Wix/AgMBAAECgYAWbKe9xeJQxql1zq26lqZ9IqCg
9Nlfz8keukPVygqWtHWYD/y9o95YAaoPq3EfG78PUo/Bm8mJ2BoFqpA/xx/EQzbx
2jz7tllmNvo+ndt5IBKd5F0adI2nsgZg14q4aXCfUIsOrVMccXGt+ZqfERXsl2Ke
AshuLjdxCp3ZAn4AQQJBAONcOVELcqEYRI9C1ahOrU9USK7o22W/mV9cLK99/GmA
1IqrjPOnQcg9tBV5dB+TNyV1fNh3yb9RfTPxsrdf06ECQQDXmmgJDy6/ediYd+Im
eZlnv43GVCirg4r1DfNYW/xVAphPybHUyPsRs5zPMlAPzpAw7XpR7vcmfrkZfpY7
VAwfAkBAZEEza9uWNmpMbLBYT5gUDJndN8PTaFVGxbM+LJ9NPhh0AawU4bzmulsD
bfdubeJDcKfgIuT3k1uzV3O3LaOhAkB4dKzhOtlMYv/vFzODeXaKr0u/Xa+nO4P8
K4IkgKf2us9r7GztTChxmF7op9xxKGAI5fTsJ81vdWdm5gF7PhbTAkBNpgEBNNEn
oUXw+z6KbRskKaAbbfAOCEa6O3RKMst0wNX6Nz6eByS/x9kQF7i3m/KpyOnJT5hw
wIHq587REp4F
-----END PRIVATE KEY-----
EOD;

    $clientAuthKey = 'd5ba7ce02076453faa0435237cb9387b';
    
    // Initialize the OPay client
    $opay = new OPayDigitalWallet($opayPublicKey, $merchantPrivateKey, $clientAuthKey);
    
    // Prepare wallet data
    $walletData = [
        'opayMerchantId' => '1200338057', // Your actual merchant ID
        'name' => 'Kabiru Adamu',
        'refid' => 'refer'.time(), // Unique reference
        'email' => 'adamkabeer24@outlook.com',
        'phone' => '2348065615684',
        'accountType' => 'Merchant', // or 'User'
        'sendPassWordFlag' => 'N'
    ];
    
    // Create the digital wallet
    $result = $opay->createDigitalWallet($walletData);
    
    // Handle the response
    if (isset($result['code']) && $result['code'] === '00000') {
        echo "Wallet created successfully!\n";
        echo "Wallet Number: " . $result['data']['depositCode'] . "\n";
    } else {
        echo "Error creating wallet: " . ($result['message'] ?? 'Unknown error') . "\n";
        if (isset($result['code'])) {
            echo "Error code: " . $result['code'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>