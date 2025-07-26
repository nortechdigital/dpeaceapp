<?php
require_once 'ArtxApiClient.php';

// Usage with more flexible error handling
try {
    $api = new ArtxApiClient(false, 'testUser1', 'Test123!');
    
    try {
        validateSslCertificate($api->getApiUrl());
    } catch (Exception $e) {
        // Log the error but continue for staging environment
        error_log("Certificate warning: " . $e->getMessage());
        
        // For production, you might want to re-throw the exception
        if ($api->isProductionEnvironment()) {
            throw $e;
        }
    }
    
    // Example 1: Get account balance
    $balance = $api->getBalance();
    echo "Current balance: {$balance['value']} {$balance['currency']}\n";
    
    // Example 2: Get operators for Nigeria
    $operators = $api->getOperators('NG');
    foreach ($operators as $op) {
        echo "Operator {$op['id']}: {$op['name']}\n";
    }
    
    // Example 3: Get products for an operator (MTN Nigeria)
    $products = $api->getOperatorProducts(1);
    foreach ($products['products'] as $id => $product) {
        echo "Product {$id}: {$product['name']}\n";
    }
    
    // Example 4: Parse a phone number
    $msisdnInfo = $api->parseMsisdn('2348034367199');
    echo "Phone number belongs to: {$msisdnInfo['operator']['name']}\n";
    
    // Example 5: Execute a transaction
    $transaction = $api->executeTransaction([
        'operator' => 1,
        'msisdn' => '2347067213153',
        'amount' => 100,
        'userReference' => 'Test' . time(), // Unique reference
        'productId' => 1
    ]);
    echo "Transaction ID: {$transaction['id']}\n";
    
    // Example 6: Check transaction status
    $status = $api->getTransaction($transaction['id']);
    echo "Transaction status: {$status['status']['name']}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    error_log("API Error: " . $e->getMessage());
}