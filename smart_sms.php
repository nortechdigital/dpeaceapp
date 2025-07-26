<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class SmartSMSSolutions {
    private $apiToken;
    private $baseUrl = 'https://app.smartsmssolutions.com/io/api/client/v1/';
    
    public function __construct($apiToken) {
        $this->apiToken = $apiToken;
    }
    
  
    public function sendSMS($sender, $recipients, $message, $type = 0, $routing = 3, $ref_id = null, $simserver_token = null, $dlr_timeout = null, $schedule = null) {
        $endpoint = $this->baseUrl . 'sms/';
        
        // Prepare recipients - convert array to comma-separated string if needed
        $to = is_array($recipients) ? implode(',', $recipients) : $recipients;
        
        // Prepare POST data
        $postData = [
            'token' => $this->apiToken,
            'sender' => $sender,
            'to' => $to,
            'message' => $message,
            'type' => $type,
            'routing' => $routing
        ];
        
        // Add optional parameters if provided
        if ($ref_id) $postData['ref_id'] = $ref_id;
        if ($simserver_token) $postData['simserver_token'] = $simserver_token;
        if ($dlr_timeout) $postData['dlr_timeout'] = $dlr_timeout;
        if ($schedule) $postData['schedule'] = $schedule;
        
        // Initialize cURL
        $ch = curl_init();
        
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development only, remove in production
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        // Execute the request
        $response = curl_exec($ch);
        
        // Check for errors
        if (curl_errno($ch)) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }
        
        // Get HTTP status code
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Close cURL
        curl_close($ch);
        
        // Decode JSON response
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode != 200 || !$decodedResponse) {
            throw new Exception('API request failed. HTTP Code: ' . $httpCode . ' Response: ' . $response);
        }
        
        return $decodedResponse;
    }
}

// Usage Example:
try {
    // Initialize with your API token
    $smsApi = new SmartSMSSolutions('0FlROPlobovx4TFwjzoyiABQKU0l7ZvYH8pNsc0t76LJqP38po');
    
    // Send SMS
    $response = $smsApi->sendSMS(
        'DPeaceApp',         // Sender ID
        '08065615684',          // Recipient (can be array for multiple)
        'Dear Kabiru Adamu, welcome to DPeace App. Better Life Begins with You!',  // Message
        0,                      // Message type (0 = Plain Text)
        2,                      // Routing option
        'ref123',               // Optional reference ID
        null                   // Optional SIM server token
        
    );
    
    // Handle response
    echo "SMS sent successfully!\n";
    print_r($response);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>