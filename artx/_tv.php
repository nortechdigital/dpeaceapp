<?php
require_once 'ArtxApiClient.php';

try {
    $api = new ArtxApiClient(false, 'testUser1', 'Test123!');
    
    // Get TV operators (productType 3 = Bill Payment, category 7 = Cable TV)
    $tvOperators = $api->getOperators(null, 3, '7.0');
    
    echo "Available TV Subscription Providers:</br>";
    foreach ($tvOperators as $operator) {
        echo "ID: {$operator['id']} - {$operator['name']}</br>";
    }
    
    // Example: Select first TV operator
    $operatorId = key($tvOperators); // Get first operator ID
    $products = $api->getOperatorProducts($operatorId, '7.0');
    
    echo "</br>Available TV Packages:</br>";
    foreach ($products['products'] as $id => $product) {
        echo "ID: $id - {$product['name']} ({$product['price']['user']} {$products['currency']['user']})</br>";
    }
    
    // Example TV subscription payment
    $transaction = $api->executeTransaction([
        'operator' => $operatorId,
        'accountId' => '1122334455', // Customer's smartcard number
        'amount' => $products['products'][key($products['products'])]['price']['user'], // Use first product's price
        'userReference' => 'TVSUB' . time(),
        'productId' => key($products['products']), // Use first product ID
        'extraParameters' => [
            'package' => 'PREMIUM' // Specific package if required
        ]
    ]);
    
    echo "</br>TV Subscription Successful!</br>";
    echo "Transaction ID: {$transaction['id']}</br>";
    echo "Operator Reference: {$transaction['operator']['reference']}</br>";
    
} catch (Exception $e) {
    echo "Error processing TV subscription: " . $e->getMessage();
    error_log("TV Subscription Error: " . $e->getMessage());
}