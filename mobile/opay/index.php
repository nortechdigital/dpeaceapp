<?php
class OPayWalletCreator {
    private $baseUrl = 'https://payapi.opayweb.com';
    private $clientAuthKey = 'd5ba7ce02076453faa0435237cb9387b'; // Replace with your actual key
    private $version = 'V1.0.1';
    private $opayPublicKey; // OPay's public key
    private $merchantPrivateKey; // Your private key
    
    public function __construct($opayPublicKeyPath, $merchantPrivateKeyPath) {
        // Load keys from files
        $this->opayPublicKey = file_get_contents($opayPublicKeyPath);
        $this->merchantPrivateKey = file_get_contents($merchantPrivateKeyPath);
        
        if (!$this->opayPublicKey || !$this->merchantPrivateKey) {
            throw new Exception("Failed to load encryption keys");
        }
    }
    
    /**
     * Create a digital wallet
     * 
     * @param array $walletParams Parameters for wallet creation
     * @return array Decrypted response from OPay
     */
    public function createWallet(array $walletParams): array {
        // Validate required parameters
        $requiredParams = ['opayMerchantId', 'name', 'accountType'];
        foreach ($requiredParams as $param) {
            if (!isset($walletParams[$param])) {
                throw new Exception("Missing required parameter: $param");
            }
        }
        
        // Ensure at least one of refId, email, or phone is provided
        if (empty($walletParams['refId']) && empty($walletParams['email']) && empty($walletParams['phone'])) {
            throw new Exception("At least one of refId, email, or phone must be provided");
        }
        
        // Prepare request data
        $requestData = [
            'opayMerchantId' => $walletParams['opayMerchantId'],
            'name' => $walletParams['name'],
            'accountType' => $walletParams['accountType'], // 'Merchant' or 'User'
        ];
        
        // Add optional parameters if provided
        if (!empty($walletParams['refId'])) {
            $requestData['refId'] = $walletParams['refId'];
        }
        if (!empty($walletParams['email'])) {
            $requestData['email'] = $walletParams['email'];
        }
        if (!empty($walletParams['phone'])) {
            $requestData['phone'] = $walletParams['phone'];
        }
        if (isset($walletParams['sendPasswordFlag'])) {
            $requestData['sendPasswordFlag'] = strtoupper($walletParams['sendPasswordFlag']) === 'Y' ? 'Y' : 'N';
        }
        
        // Encrypt the request data
        $encryptedData = $this->encryptRequestData($requestData);
        
        // Create the request body
        $requestBody = [
            'paramContent' => $encryptedData['encryptedData'],
            'sign' => $encryptedData['signature']
        ];
        
        // Prepare headers
        $timestamp = round(microtime(true) * 1000);
    	$timestamp = (time() + 3600) * 1000;
        $headers = [
            'clientAuthKey: ' . $this->clientAuthKey,
            'version: ' . $this->version,
            'bodyFormat: JSON',
            'timestamp: ' . (string)$timestamp,
            'Content-Type: application/json'
        ];
        
        // Make the API request
        $response = $this->makeApiRequest(
            '/api/v2/third/depositcode/generateStaticDepositCode',
            $requestBody,
            $headers
        );
        
        // Decrypt and return the response
        return $this->decryptResponseData($response);
    }
    
    /**
     * Encrypt request data using OPay's public key and sign with merchant private key
     */
    private function encryptRequestData(array $data): array {
        $jsonData = json_encode($data);
        $timestamp = round(microtime(true) * 1000);
    	$timestamp = (time() + 3600) * 1000;
        
        // Encrypt with OPay's public key
        openssl_public_encrypt($jsonData, $encryptedData, $this->opayPublicKey);
        $encryptedDataBase64 = base64_encode($encryptedData);
        
        // Create signature (paramContent + timestamp)
        $dataToSign = $encryptedDataBase64 . $timestamp;
        $signature = $this->signData($dataToSign);
        
        return [
            'encryptedData' => $encryptedDataBase64,
            'signature' => $signature
        ];
    }

    /**
     * Sign data using SHA256withRSA with proper key formatting
     */
    private function signData(string $data): string {
        // Ensure private key is in proper PEM format
        $privateKey = $this->ensureProperKeyFormat($this->merchantPrivateKey, 'PRIVATE');
        
        // Load private key
        $pkey = openssl_pkey_get_private($privateKey);
        if ($pkey === false) {
            throw new Exception("Private key loading failed: " . openssl_error_string());
        }
        
        // Generate signature
        $signature = '';
        if (!openssl_sign($data, $signature, $pkey, OPENSSL_ALGO_SHA256)) {
            throw new Exception("Signing failed: " . openssl_error_string());
        }
        
        if (PHP_MAJOR_VERSION < 8) {
            openssl_pkey_free($pkey);
        }
        
        return base64_encode($signature);
    }

    /**
     * Ensure key is in proper PEM format
     */
    private function ensureProperKeyFormat(string $key, string $type): string {
        $beginMarker = "-----BEGIN $type KEY-----";
        $endMarker = "-----END $type KEY-----";
        
        if (strpos($key, $beginMarker) !== false) {
            return $key; // Already in PEM format
        }
        
        // Convert to PEM format
        return $beginMarker . "\n" . 
               chunk_split($key, 64, "\n") . 
               $endMarker . "\n";
    }

    /**
     * Verify signature with OPay's public key
     */
    private function verifySignature(string $data, string $timestamp, string $signature): bool {
        $dataToVerify = $data . $timestamp;
        $signature = base64_decode($signature);
        
        // Ensure public key is in proper PEM format
        $publicKey = $this->ensureProperKeyFormat($this->opayPublicKey, 'PUBLIC');
        
        $result = openssl_verify(
            $dataToVerify,
            $signature,
            $publicKey,
            OPENSSL_ALGO_SHA256
        );
        
        if ($result === 1) {
            return true;
        } elseif ($result === 0) {
            throw new Exception("Signature verification failed. Please check your keys.");
        } else {
            throw new Exception("Error verifying signature: " . openssl_error_string());
        }
    }

    /**
     * Make API request to OPay
     */
    private function makeApiRequest(string $endpoint, array $body, array $headers): array {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_VERBOSE => true, // Enable verbose output
            CURLOPT_STDERR => fopen('curl_debug.log', 'w+'), // Log debug info
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("CURL error: $error");
        }
        
        curl_close($ch);
        
        // Log the raw response for debugging
        file_put_contents('opay_response.log', $response, FILE_APPEND);
        
        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to decode JSON response: " . json_last_error_msg() . "\nRaw response: " . substr($response, 0, 500));
        }
        
        // Additional response validation
        if (!isset($decodedResponse['code'])) {
            throw new Exception("Invalid API response format. Missing 'code' field. Full response: " . json_encode($decodedResponse));
        }
        
        // Check if the request was successful
        if ($decodedResponse['code'] !== '00000') {
            $message = $decodedResponse['message'] ?? 'No error message provided';
            throw new Exception("OPay API error ({$decodedResponse['code']}): $message");
        }
        
        return $decodedResponse;
    }
    
    private function decryptResponseData(array $response): array {
        // First check if this is an error response
        if ($response['code'] !== '00000') {
            return $response; // Return the error response as-is
        }
        
        // Then check for required fields
        if (!isset($response['data']) || !isset($response['timestamp']) || !isset($response['sign'])) {
            throw new Exception("Invalid response format from OPay. Missing required fields. Response: " . json_encode($response));
        }
        
        try {
            // Verify the signature first
            $this->verifySignature(
                $response['data'],
                $response['timestamp'],
                $response['sign']
            );
            
            // Decrypt the data
            $decryptedData = $this->decryptData($response['data']);
            
            return [
                'code' => $response['code'],
                'message' => $response['message'] ?? '',
                'data' => $decryptedData
            ];
        } catch (Exception $e) {
            // If decryption fails, return the original response with error
            return [
                'code' => 'DECRYPT_ERROR',
                'message' => $e->getMessage(),
                'original_response' => $response
            ];
        }
    }
}

// Example Usage with additional key verification:
try {
    // First verify the keys are readable
    if (!file_exists('./client_public_key_php_dotnet.pem') || !file_exists('./client_private_key_php_dotnet.pem')) {
        throw new Exception("Key files not found. Please check paths.");
    }

    $opayPublicKey = file_get_contents('./opay_public_key.pem');
    $merchantPrivateKey = file_get_contents('./client_private_key_php_dotnet.pem');

    if (empty($opayPublicKey) || empty($merchantPrivateKey)) {
        throw new Exception("One or both key files are empty");
    }

    echo "Keys loaded successfully. Testing key formats...\n";

    // Initialize the wallet creator
    $walletCreator = new OPayWalletCreator(
        './client_public_key_php_dotnet.pem',
        './client_private_key_php_dotnet.pem'
    );

    $walletParams = [
        'opayMerchantId' => '256625060262101',
        'name' => 'John Doe',
        'refId' => 'refer1308698638',
        'email' => 'user@example.com',
        'phone' => '2341231231231',
        'accountType' => 'Merchant',
        'sendPasswordFlag' => 'N'
    ];

    echo "Attempting wallet creation...\n";
    $result = $walletCreator->createWallet($walletParams);

    if ($result['code'] === '00000') {
        echo "Wallet created successfully!\n";
        print_r($result['data']);
    } else {
        echo "Error creating wallet:\n";
        print_r($result);
        
        // Additional troubleshooting for signature errors
        if ($result['code'] === 'C_1112') {
            echo "\nSignature Verification Failed Troubleshooting:\n";
            echo "1. Verify your private key matches what's registered with OPay\n";
            echo "2. Check the key format (should be PEM)\n";
            echo "3. Ensure you're using the correct key pair\n";
            echo "4. Confirm the signing algorithm is SHA256withRSA\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    if (file_exists('curl_debug.log')) {
        echo "\nCURL Debug:\n" . file_get_contents('curl_debug.log') . "\n";
    }
    
    if (file_exists('opay_response.log')) {
        echo "\nOPay Response:\n" . file_get_contents('opay_response.log') . "\n";
    }
}
?>