<?php
class ArtxApiClient {
    private $apiUrl;
    private $username;
    private $password;
    private $version = 5;
    
 

    public function __construct($isProduction = true, $username, $password) {
        $this->apiUrl = $isProduction 
            ? 'https://artxh1.sochitel.com/api.php' 
            : 'https://artx.sochitel.com/staging.php';
        $this->username = $username;
        $this->password = $password;
    }
    
    public function getApiUrl() {
        return $this->apiUrl;
    }
    
    private function generateSalt($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    private function generatePasswordHash($salt) {
        $sha1Password = sha1($this->password);
        return sha1($salt . $sha1Password);
    }
    
    private function verifyCertificate($ch) {
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
                'signature' => ''
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
    
    public function lookupAccount($accountId, $productId) {
        return $this->makeRequest('lookupAccount', [
            'accountId' => $accountId,
            'productId' => $productId
        ]);
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

function validateSslCertificate($url) {
    if (getenv('APP_ENV') === 'development') {
        error_log("Skipping certificate validation in development mode");
        return true;
    }
    
    $trustedFingerprints = [
        'd9fe89936f125e0bf13fbb405d06fba9b264e7cbf0b78fa88436018bf538e212',
        '1b9b0a9d38102f1a53cbcb4390526cc3180b53c635875320c344394c28455a7a'
    ];

    $context = stream_context_create([
        'ssl' => [
            'capture_peer_cert' => true,
            'verify_peer' => true,
            'verify_peer_name' => true
        ]
    ]);

    $client = stream_socket_client(
        'ssl://' . parse_url($url, PHP_URL_HOST) . ':443',
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if (!$client) {
        throw new Exception("SSL connection failed: $errstr ($errno)");
    }

    $params = stream_context_get_params($client);
    $cert = $params['options']['ssl']['peer_certificate'];
    
    if (!$cert) {
        throw new Exception("Could not retrieve SSL certificate");
    }

    openssl_x509_export($cert, $certificate);
    $fingerprint = openssl_x509_fingerprint($certificate, 'sha256');

    if (!in_array(strtolower($fingerprint), array_map('strtolower', $trustedFingerprints))) {
        $certDetails = openssl_x509_parse($certificate);
        $debugInfo = [
            'fingerprint_received' => $fingerprint,
            'fingerprint_expected' => $trustedFingerprints,
            'certificate_details' => $certDetails,
            'valid_from' => date('Y-m-d H:i:s', $certDetails['validFrom_time_t']),
            'valid_to' => date('Y-m-d H:i:s', $certDetails['validTo_time_t'])
        ];
        
        error_log("Certificate validation debug: " . print_r($debugInfo, true));
        throw new Exception("SSL certificate fingerprint not trusted. Received: $fingerprint");
    }

    return true;
}