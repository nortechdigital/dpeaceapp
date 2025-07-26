<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "../conn.php"; // contains $conn variable
include "../_/ac_config.php"; // contains API credentials

class SwiftIntegration {
    private $username;
    private $password;
    private $partner;
    private $baseUrl = "http://swiftng.com:3000";
    
    public function __construct($username, $password, $partner) {
        $this->username = $username;
        $this->password = $password;
        $this->partner = $partner;
    }
    
    public function validateCustomer($customerId) {
        $url = $this->baseUrl . "/customer.aspx?" . http_build_query([
            'Username' => $this->username,
            'Password' => $this->password,
            'Partner' => $this->partner,
            'customer_id' => $customerId
        ]);
        
        $response = $this->makeRequest($url);
        
        if ($response === false) {
            return ['error' => 'API request failed'];
        }
        
        $xml = simplexml_load_string($response);
        if ($xml === false) {
            return ['error' => 'Invalid XML response'];
        }
        
        $result = [
            'customer_id' => (string)$xml->Customer->CustomerId,
            'status_code' => (string)$xml->Customer->StatusCode,
            'status_description' => (string)$xml->Customer->StatusDescription
        ];
        
        if ($result['status_code'] == '0') {
            $result['first_name'] = (string)$xml->Customer->FirstName;
            $result['last_name'] = (string)$xml->Customer->LastName;
        }
        
        return $result;
    }

    /**
     * Process payment with enhanced error handling
     * @param array $paymentData
     * @return array
     */
    public function processPayment($paymentData) {
        $url = $this->baseUrl . "/ISWPayment.ashx?" . http_build_query([
            'Username' => $this->username,
            'Password' => $this->password,
            'Partner' => $this->partner
        ]);
        
        $xmlRequest = $this->buildPaymentXml($paymentData);var_dump($xmlRequest);
        
        // Add debug logging
        error_log("Sending payment request to: " . $url);
        error_log("Request XML: " . $xmlRequest);
        
        $response = $this->makeRequest($url, $xmlRequest);
        
        if ($response === false) {
            return ['error' => 'API request failed', 'details' => curl_error($ch)];
        }
        
        // Log raw response for debugging
        error_log("Raw API response: " . $response);
        
        $xml = simplexml_load_string($response);
        if ($xml === false) {
            return [
                'error' => 'Invalid XML response',
                'raw_response' => htmlentities($response)
            ];
        }
        
        return [
            'payment_log_id' => (string)$xml->Payments->Payment->PaymentLogId,
            'status' => (string)$xml->Payments->Payment->Status,
            'status_description' => (string)$xml->Payments->Payment->StatusDesc,
            'raw_response' => $response // Include for debugging
        ];
    }
    
    private function buildPaymentXml($data) {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><PaymentNotificationRequest></PaymentNotificationRequest>');
        $payments = $xml->addChild('Payments');
        $payment = $payments->addChild('Payment');
        
        // Required fields
        $payment->addChild('PaymentLogId', $data['payment_log_id'] ?? '');
        $payment->addChild('CustReference', $data['customer_reference']);
        $payment->addChild('AlternateCustReference', $data['alternate_cust_reference'] ?? '');
        $payment->addChild('Amount', $data['amount']);
        $payment->addChild('PaymentMethod', $data['payment_method'] ?? '');
        $payment->addChild('PaymentReference', $data['payment_reference'] ?? '');
        $payment->addChild('TerminalId', $data['terminal_id'] ?? '');
        $payment->addChild('ChannelName', $data['channel_name'] ?? '');
        $payment->addChild('Location', $data['location'] ?? '');
        $payment->addChild('PaymentDate', $data['payment_date'] ?? date('Y-m-d H:i:s A'));
        
        // Optional fields
        $optionalFields = [
            'AlternateCustReference', 'PaymentMethod', 'PaymentReference', 'TerminalId',
            'ChannelName', 'Location', 'InstitutionId', 'InstitutionName', 'BranchName',
            'BankName', 'CustomerName', 'OtherCustomerInfo', 'ReceiptNo', 'CollectionsAccount',
            'BankCode', 'CustomerAddress', 'CustomerPhoneNumber', 'DepositorName',
            'DepositSlipNumber', 'PaymentCurrency'
        ];
        
        foreach ($optionalFields as $field) {
            if (isset($data[strtolower($field)])) {
                $payment->addChild($field, $data[strtolower($field)]);
            }
        }
        
        $payment->addChild('IsReversal', isset($data['is_reversal']) ? ($data['is_reversal'] ? 'true' : 'false') : 'false');
        
        if (isset($data['payment_items']) && is_array($data['payment_items'])) {
            $paymentItems = $payment->addChild('PaymentItems');
            foreach ($data['payment_items'] as $item) {
                $paymentItem = $paymentItems->addChild('PaymentItem');
                $paymentItem->addChild('ItemName', $item['name']);
                $paymentItem->addChild('ItemCode', $item['code'] ?? '');
                $paymentItem->addChild('ItemAmount', $item['amount']);
            }
        }
        
        $payment->addChild('PaymentDate', date('Y-m-d h:i:s A'));
        
        return $xml->asXML();
    }

    private function makeRequest($url, $postData = null) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FAILONERROR => true,
            CURLOPT_SSL_VERIFYPEER => false, // Only for testing, remove in production
            CURLOPT_HTTPHEADER => $postData ? ['Content-Type: application/xml'] : []
        ]);
        
        if ($postData !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($error) {
            error_log("CURL Error: " . $error);
            return false;
        }
        
        return $response;
    }
}

// Usage with credential verification
$swift = new SwiftIntegration(SWIFT_USERNAME, SWIFT_PASSWORD, SWIFT_PARTNER);

// First verify credentials work with customer validation
$validationResult = $swift->validateCustomer('289728');
if (isset($validationResult['error'])) {
    die("Customer validation failed: " . print_r($validationResult, true));
}

// Only proceed if customer validation succeeds
if ($validationResult['status_code'] == '0') {
    $paymentData = [
        'payment_log_id' => 'PYMT-' . time(), // Generate a unique ID
        'customer_reference' => '289728',
        'amount' => 7000,
        'payment_method' => 'Debit Card',
        'payment_items' => [
            [
                'name' => 'Data',
                'code' => 'Data',
                'amount' => 7000
            ]
        ],
        'institution_id' => 'SWIFT',
        'institution_name' => 'SWIFT Networks'
    ];
    
    $paymentResult = $swift->processPayment($paymentData);
    print_r($paymentResult);
} else {
    echo "Cannot process payment - customer validation failed: " . $validationResult['status_description'];
}
?>