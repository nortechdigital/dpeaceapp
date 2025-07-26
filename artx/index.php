<?php
require_once 'ArtxApiClient.php';

try {
    $api = new ArtxApiClient(false, 'testUser1', 'Test123!');
    
    // Get electricity operators (productType 3 = Bill Payment)
    $electricityOperators = $api->getOperators(null, 3, '8.1'); // 8.1 = Electricity
    
    echo "Available Electricity Providers:\n";
    foreach ($electricityOperators as $operator) {
        echo "ID: {$operator['id']} - {$operator['name']}\n";
    }
    
    // Example: Nigeria Eko Electricity (ID 536)
    $operatorId = 536;
    $products = $api->getOperatorProducts($operatorId, '8.1');
    
    echo "\nAvailable Products for Eko Electricity:\n";
    foreach ($products['products'] as $id => $product) {
        echo "ID: $id - {$product['name']}\n";
    }
    
    // Example electricity bill payment
    $transaction = $api->executeTransaction([
        'operator' => $operatorId,
        'accountId' => '1234567890', // Customer's meter number
        'amount' => 1000, // Amount in NGN
        'userReference' => 'ELEC' . time(),
        'extraParameters' => [
            'accountType' => 'PREPAID' // or 'POSTPAID' depending on customer
        ]
    ]);
    
    echo "\nElectricity Payment Successful!\n";
    echo "Transaction ID: {$transaction['id']}\n";
    echo "Operator Reference: {$transaction['operator']['reference']}\n";
    
} catch (Exception $e) {
    echo "Error processing electricity payment: " . $e->getMessage();
    error_log("Electricity Payment Error: " . $e->getMessage());
}