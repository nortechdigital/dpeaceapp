<?php
class PalmPayNotificationHandler {
    /**
     * Process PalmPay payment notification
     * 
     * @param array $notificationData Received notification data
     * @return bool Whether the notification was valid and processed successfully
     */
    public static function handleNotification($notificationData) {
        try {
            // Validate required parameters
            $requiredParams = [
                'outOrderNo',
                'orderNo',
                'appId',
                'amount',
                'orderStatus',
                'completedTime',
                'sign'
            ];
            
            foreach ($requiredParams as $param) {
                if (!isset($notificationData[$param])) {
                    throw new Exception("Missing required parameter: $param");
                }
            }
            
            // Verify the signature
            if (!self::verifyNotificationSignature($notificationData)) {
                throw new Exception("Invalid signature");
            }
            
            // Process the notification based on order status
            $orderStatus = (int)$notificationData['orderStatus'];
            $outOrderNo = $notificationData['outOrderNo'];
            $orderNo = $notificationData['orderNo'];
            $amount = $notificationData['amount'] / 100; // Convert from cents to currency unit
            
            switch ($orderStatus) {
                case 1: // Success
                    self::processSuccessfulPayment($outOrderNo, $orderNo, $amount, $notificationData);
                    break;
                case 2: // Processing
                    self::logPaymentProcessing($outOrderNo, $orderNo);
                    break;
                case 3: // Failed
                    $errorMsg = $notificationData['errorMsg'] ?? 'Payment failed';
                    self::processFailedPayment($outOrderNo, $orderNo, $errorMsg);
                    break;
                default:
                    throw new Exception("Unknown order status: $orderStatus");
            }
            
            return true;
        } catch (Exception $e) {
            error_log("PalmPay notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify the notification signature
     */
    private static function verifyNotificationSignature($notificationData) {
        // Create the string to verify (sorted non-empty parameters except sign)
        $verifyData = $notificationData;
        unset($verifyData['sign']);
        
        // Remove empty values
        $verifyData = array_filter($verifyData, function($value) {
            return !empty($value) && trim($value) !== '';
        });
        
        // Sort by key
        ksort($verifyData);
        
        // Create key=value pairs
        $parts = [];
        foreach ($verifyData as $key => $value) {
            $parts[] = $key . '=' . trim($value);
        }
        $stringToVerify = implode('&', $parts);
        
        // Verify the signature
        $signature = urldecode($notificationData['sign']);
        return PalmPaySignature::verifyCallbackSignature($stringToVerify, $signature);
    }
    
    /**
     * Process successful payment
     */
    private static function processSuccessfulPayment($outOrderNo, $orderNo, $amount, $notificationData) {
        // Implement your business logic here
        // Example: Update order status in database, deliver product, etc.
        
        error_log("Payment successful - Order: $outOrderNo, PalmPay Order: $orderNo, Amount: $amount");
        
        // You might want to:
        // 1. Verify this is the first notification for this order
        // 2. Check amount matches expected amount
        // 3. Update your database
        // 4. Trigger fulfillment process
    }
    
    /**
     * Log processing payment
     */
    private static function logPaymentProcessing($outOrderNo, $orderNo) {
        // Payment is still being processed
        error_log("Payment processing - Order: $outOrderNo, PalmPay Order: $orderNo");
    }
    
    /**
     * Process failed payment
     */
    private static function processFailedPayment($outOrderNo, $orderNo, $errorMsg) {
        // Implement your failure handling logic here
        error_log("Payment failed - Order: $outOrderNo, PalmPay Order: $orderNo, Error: $errorMsg");
        
        // You might want to:
        // 1. Update order status to failed
        // 2. Notify customer
        // 3. Log the failure reason
    }
}

// Example usage in your notification endpoint:
try {
    // Get the notification data (assuming JSON payload)
    $json = file_get_contents('php://input');
    $notificationData = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON data");
    }
    
    // Process the notification
    $result = PalmPayNotificationHandler::handleNotification($notificationData);
    
    // Respond to PalmPay (must be exactly "success" for successful acknowledgement)
    if ($result) {
        header('Content-Type: text/plain');
        echo "success";
        exit;
    } else {
        header('HTTP/1.1 400 Bad Request');
        echo "Error processing notification";
        exit;
    }
} catch (Exception $e) {
    error_log("Notification endpoint error: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo "Error processing notification";
    exit;
}
?>