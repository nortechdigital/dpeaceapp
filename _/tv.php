<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "../conn.php"; // contains $conn variable
include "../_/ac_config.php"; // contains API credentials

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
    
    /**
     * Validate customer smartcard number for TV services
     * 
     * @param string $billerId - The biller ID (e.g., "DSTV", "GOTV", "STARTIMES")
     * @param string $customerId - Customer's smartcard number
     * @param string $requestId - Unique request ID from your system
     * @return array API response
     */
    public function validateCustomer($billerId, $customerId, $requestId) {
        $endpoint = "{$this->baseUrl}{$this->envUri}tv/validate";
        
        $payload = [
            'billerId' => $billerId,
            'customerId' => $customerId,
            'requestId' => $requestId
        ];
        
        return $this->makeRequest($endpoint, $payload);
    }
    
    /**
     * Make payment for TV subscription
     * 
     * @param string $billerId - The biller ID
     * @param string $customerId - Customer's smartcard number
     * @param string $requestId - Unique request ID from your system
     * @param string $customerName - Customer name from validation
     * @param float $amount - Amount to pay
     * @param string|null $bouquetCode - Bouquet code (for subscription changes)
     * @param string|null $customerNumber - Customer number from validation (for DSTV/GOTV)
     * @param string|null $addonCode - Addon code (optional)
     * @return array API response
     */
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
    
    /**
     * Retrieve available bouquets for a TV service
     * 
     * @param string $billerId - The biller ID (e.g., "DSTV", "GOTV")
     * @param string|null $type - Optional bouquet type filter
     * @return array API response
     */
    public function getBouquets($billerId, $type = null) {
        $endpoint = "{$this->baseUrl}{$this->envUri}bouquet/tv/{$billerId}";
        
        if ($type !== null) {
            $endpoint .= "?type=" . urlencode($type);
        }
        
        return $this->makeRequest($endpoint, null, 'GET');
    }
    
    /**
     * Requery a transaction
     * 
     * @param string $requestId - The original request ID
     * @return array API response
     */
    public function requeryTransaction($requestId) {
        $endpoint = "{$this->baseUrl}{$this->envUri}requery?requestId=" . urlencode($requestId);
        return $this->makeRequest($endpoint, null, 'GET');
    }
    
    /**
     * Make API request
     */
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

// Example usage:

// Initialize the service
$tvService = new VAS2NetsTVService(
    VAS2NETS_BASE_URL,           // BASE_URL - provided by VAS2Nets
    VAS2NETS_ENV_URI,            // ENV_URI - '/sandbox' for test, '/prod' for production
    VAS2NETS_USERNAME,           // API username
    VAS2NETS_PASSWORD            // API password
);

try {
    // Example 1: Validate a DSTV smartcard
    $validation = $tvService->validateCustomer('DSTV', '7030935900', 'req12345');
    print_r($validation);
    
    if ($validation['data']['status'] === 'Success') {
        // Example 2: Get available DSTV bouquets
        $bouquets = $tvService->getBouquets('DSTV');
        print_r($bouquets);
        
        // Example 3: Subscribe to a DSTV bouquet
        $payment = $tvService->makePayment(
            'DSTV', 
            '7030935900', 
            'req67890',
            $validation['data']['customerName'],
            10450, // amount
            'DSTVCNFM', // bouquet code
            $validation['data']['customerNumber'],
            'FRN11E36' // optional addon code
        );
        print_r($payment);
        
        // Example 4: Requery the transaction if needed
        $requery = $tvService->requeryTransaction('req67890');
        print_r($requery);
    }
    
    // Example 5: Startimes payment (no bouquet needed)
    $startimesPayment = $tvService->makePayment(
        'STARTIMES',
        '8383421134',
        'req54321',
        'Customer Name',
        5000 // amount
    );
    print_r($startimesPayment);
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}