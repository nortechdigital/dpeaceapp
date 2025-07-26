<?php
// ARTX Electricity Payment Integration
class ARTXElectricityPayment {
    private $apiEndpoint = 'https://artxh1.sochitel.com/api.php';
    private $username = 'dpeaceapp.api.ngn';
    private $password = 'login@DPeaceAdmin1234';
    private $operatorId = 536; // Nigeria Eko Elec. Prepaid
    private $maxRetries = 3;
    private $retryDelay = 5; // seconds

    public function processPayment($accountNumber, $amount, $productId = null) {
        try {
            // 1. Validate account number format
            $this->validateAccountNumber($accountNumber);
            
            // 2. Get available products for the operator
            $products = $this->getOperatorProducts();
            
            // 3. Use specified product ID or first available
            $productId = $productId ?? $products[0]['id'];
            
            // 4. Validate customer account
            $accountInfo = $this->validateAccount($accountNumber, $productId);
            
            // 5. Execute payment transaction
            $transaction = $this->execTransaction($accountNumber, $amount, $productId);
            
            return [
                'success' => true,
                'transaction_id' => $transaction['result']['id'],
                'account_info' => $accountInfo['result']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => method_exists($e, 'getCode') ? $e->getCode() : null
            ];
        }
    }

    private function validateAccountNumber($accountNumber) {
        if (!preg_match('/^[0-9]{11,13}$/', $accountNumber)) {
            throw new Exception("Invalid account number format. Must be 11-13 digits.", 400);
        }
    }

    private function getOperatorProducts() {
        $payload = [
            'auth' => $this->getAuthPayload(),
            'version' => 5,
            'command' => 'getOperatorProducts',
            'operator' => $this->operatorId
        ];
        
        $response = $this->sendRequestWithRetry($payload);
        
        if (empty($response['result'])) {
            throw new Exception("No products available for this operator", 404);
        }
        
        return $response['result'];
    }

    private function validateAccount($accountNumber, $productId) {
        $payload = [
            'auth' => $this->getAuthPayload(),
            'version' => 5,
            'command' => 'lookupAccount',
            'accountId' => $accountNumber,
            'productId' => $productId
        ];
        
        return $this->sendRequestWithRetry($payload);
    }

    private function execTransaction($accountNumber, $amount, $productId) {
        $payload = [
            'auth' => $this->getAuthPayload(),
            'version' => 5,
            'command' => 'execTransaction',
            'operator' => $this->operatorId,
            'accountId' => $accountNumber,
            'amount' => $amount,
            'productId' => $productId,
            'userReference' => 'ELEC_' . time() . '_' . bin2hex(random_bytes(4))
        ];
        
        return $this->sendRequestWithRetry($payload);
    }

    private function getAuthPayload() {
        $salt = bin2hex(random_bytes(20));
        return [
            'username' => $this->username,
            'salt' => $salt,
            'password' => $this->calculateHash($this->password, $salt),
            'signature' => ''
        ];
    }

    private function calculateHash($password, $salt) {
        return sha1($salt . sha1($password));
    }

    private function sendRequestWithRetry($payload, $currentAttempt = 1) {
        $ch = curl_init($this->apiEndpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true) ?? [];
        
        // Check for API errors
        if (!isset($result['status']) || $result['status']['type'] !== 0) {
            if ($currentAttempt < $this->maxRetries && $this->isRetryableError($result)) {
                sleep($this->retryDelay);
                return $this->sendRequestWithRetry($payload, $currentAttempt + 1);
            }
            
            $errorMsg = $result['status']['name'] ?? 'API request failed';
            $errorCode = $result['status']['id'] ?? $httpCode;
            throw new Exception($errorMsg, $errorCode);
        }
        
        return $result;
    }

    private function isRetryableError($response) {
        $retryableCodes = [22, 60]; // Temporary failures
        return isset($response['status']['id']) && in_array($response['status']['id'], $retryableCodes);
    }
}

// ===== USAGE EXAMPLE =====
$payment = new ARTXElectricityPayment();

// Process payment (in practice, get these from user input)
$result = $payment->processPayment(
    accountNumber: '0101150287452',
    amount: 1000, // Amount in NGN
    productId: 4268 // Optional product ID
);

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);