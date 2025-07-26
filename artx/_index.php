<?php
class ArtxApiClient {
    private $apiUrl;
    private $username;
    private $password;
    private $version = 5;
    
    public function __construct($isProduction = false, $username, $password) {
        $this->apiUrl = $isProduction 
            ? 'https://artxbapi2.sochitel.com/api.php' 
            : 'https://artx.sochitel.com/staging.php';
        $this->username = $username;
        $this->password = $password;
    }
    
    private function generateSalt($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    private function generatePasswordHash($salt) {
        $sha1Password = sha1($this->password);
        return sha1($salt . $sha1Password);
    }
    
    private function verifyCertificate($ch) {
        // This is a placeholder - actual verification happens in the validateSslCertificate function
        // We'll just ensure we're using TLS 1.2+
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    }
    
    private function makeRequest($command, $extraParams = []) {
        $salt = $this->generateSalt();
        $passwordHash = $this->generatePasswordHash($salt);
        
        $payload = [
            'auth' => [
                'username' => $this->username,
                'salt' => $salt,
                'password' => $passwordHash,
                'signature' => '' // Optional, implement if needed
            ],
            'version' => $this->version,
            'command' => $command
        ];
        
        $payload = array_merge($payload, $extraParams);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        // Set SSL verification options
        $this->verifyCertificate($ch);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("CURL Error: " . $error);
        }
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode !== 200 || !$decodedResponse) {
            throw new Exception("Invalid API response: " . $response);
        }
        
        if ($decodedResponse['status']['type'] !== 0) {
            throw new Exception("API Error {$decodedResponse['status']['id']}: {$decodedResponse['status']['name']}");
        }
        
        return $decodedResponse['result'];
    }
    
    // API Command Methods
    public function getBalance() {
        return $this->makeRequest('getBalance');
    }
    
    public function getOperators($country = null, $productType = null, $productCategory = null) {
        $params = [];
        if ($country) $params['country'] = $country;
        if ($productType) $params['productType'] = $productType;
        if ($productCategory) $params['productCategory'] = $productCategory;
        
        return $this->makeRequest('getOperators', $params);
    }
    
    public function getOperatorProducts($operatorId, $productCategory = null) {
        $params = ['operator' => $operatorId];
        if ($productCategory) $params['productCategory'] = $productCategory;
        
        return $this->makeRequest('getOperatorProducts', $params);
    }
    
    public function parseMsisdn($msisdn) {
        return $this->makeRequest('parseMsisdn', ['msisdn' => $msisdn]);
    }
    
    public function executeTransaction($params) {
        $required = ['operator', 'amount', 'userReference'];
        foreach ($required as $field) {
            if (!isset($params[$field])) {
                throw new Exception("Missing required parameter: $field");
            }
        }
        
        return $this->makeRequest('execTransaction', $params);
    }
    
    public function getTransaction($transactionId = null, $userReference = null) {
        if (!$transactionId && !$userReference) {
            throw new Exception("Either transactionId or userReference must be provided");
        }
        
        $params = [];
        if ($transactionId) $params['id'] = $transactionId;
        if ($userReference) $params['userReference'] = $userReference;
        
        return $this->makeRequest('getTransaction', $params);
    }
    
    public function checkTransactionsStatus($transactionIds = [], $userReferences = []) {
        $params = [];
        if (!empty($transactionIds)) {
            $params['id'] = implode(',', $transactionIds);
        }
        if (!empty($userReferences)) {
            $params['userReference'] = implode(',', $userReferences);
        }
        
        if (empty($params)) {
            throw new Exception("Either transactionIds or userReferences must be provided");
        }
        
        return $this->makeRequest('checkTransactionsStatus', $params);
    }
}

// Modified SSL certificate validation function
function validateSslCertificate($url) {
    $expectedFingerprint = "d9fe89936f125e0bf13fbb405d06fba9b264e7cbf0b78fa88436018bf538e212";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_CERTINFO, true);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        curl_close($ch);
        throw new Exception("SSL connection error: " . curl_error($ch));
    }
    
    $certInfo = curl_getinfo($ch, CURLINFO_CERTINFO);
    curl_close($ch);
    
    if (empty($certInfo)) {
        throw new Exception("No certificate information received");
    }
    
    // Get the leaf certificate (first in the chain)
    $certificate = $certInfo[0]['Cert'];
    
    // Calculate fingerprint
    $certData = "-----BEGIN CERTIFICATE-----\n" . 
                chunk_split($certificate, 64, "\n") . 
                "-----END CERTIFICATE-----\n";
    
    $fingerprint = openssl_x509_fingerprint($certData, 'sha256');
    
    if (strtolower($fingerprint) !== strtolower($expectedFingerprint)) {
        throw new Exception("SSL certificate fingerprint mismatch");
    }
    
    return true;
}

// Helper function to format product categories
function getProductCategoryName($categoryId) {
    $categories = [
        '1.0' => 'Mobile Top Up',
        '2.0' => 'Mobile PIN',
        '3.0' => 'Bill Payment',
        '4.0' => 'Mobile Data',
        '12.0' => 'Retail Gift Cards',
        '13.0' => 'eVouchers',
        // Add more categories as needed
    ];
    
    return $categories[$categoryId] ?? 'Unknown Category';
}

// Initialize the client
$api = new ArtxApiClient(
    false, // Use staging environment
    'testUser1', 
    'Test123!'
);

try {
    // Example 1: Get account balance
    $balance = $api->getBalance();
    echo "Current balance: {$balance['value']} {$balance['currency']}\n";
    
    // Example 2: Get operators for Nigeria
    $operators = $api->getOperators('NG');
    foreach ($operators as $op) {
        echo "Operator {$op['id']}: {$op['name']}\n";
    }
    
    // Example 3: Get products for an operator (MTN Nigeria)
    $products = $api->getOperatorProducts(1); // 1 = MTN Nigeria
    foreach ($products['products'] as $id => $product) {
        echo "Product {$id}: {$product['name']}\n";
    }
    
    // Example 4: Parse a phone number
    $msisdnInfo = $api->parseMsisdn('2348034367199');
    echo "Phone number belongs to: {$msisdnInfo['operator']['name']}\n";
    
    // Example 5: Execute a transaction
    $transaction = $api->executeTransaction([
        'operator' => 1, // MTN Nigeria
        'msisdn' => '2347067213153',
        'amount' => 100,
        'userReference' => 'Test123',
        'productId' => 1 // Optional
    ]);
    echo "Transaction ID: {$transaction['id']}\n";
    
    // Example 6: Check transaction status
    $status = $api->getTransaction($transaction['id']);
    echo "Transaction status: {$status['status']['name']}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Example of comprehensive error handling
try {
    $api = new ArtxApiClient(false, 'testUser1', 'Test123!');
    
    // Validate SSL certificate first
    validateSslCertificate($api->getApiUrl());
    
    // Execute API calls
    $balance = $api->getBalance();
    
    // Process response
    if ($balance['value'] < 100) {
        throw new Exception("Insufficient balance for transaction");
    }
    
    // More API calls...
    
} catch (Exception $e) {
    // Log error
    error_log("ARTX API Error: " . $e->getMessage());
    
    // Display user-friendly message
    if (strpos($e->getMessage(), 'Insufficient balance') !== false) {
        echo "You don't have enough funds for this transaction.";
    } elseif (strpos($e->getMessage(), 'SSL certificate') !== false) {
        echo "Security verification failed. Please contact support.";
    } else {
        echo "An error occurred while processing your request. Please try again later.";
    }
    
    // Optionally send alert to admin
    // mail('admin@example.com', 'API Error', $e->getMessage());
}