<?php
require_once 'ArtxApiClient.php';


try {
    $api = new ArtxApiClient(true, 'dpeaceapp.api.ngn', 'login@DPeaceAdmin1234');
    
    // Get electricity operators (productType 3 = Bill Payment)
    $electricityOperators = $api->getOperators(null, 3, '8.1'); // 8.1 = Electricity
   
    echo "Available Electricity Providers:</br>";
    foreach ($electricityOperators as $operator) {
        echo "ID: {$operator['id']} - {$operator['name']} {$operator['currency']}</br>";
    }
    
    // Example: Nigeria Eko Electricity (ID 536)
    $operatorId = 672;
    $products = $api->getOperatorProducts($operatorId, '8.1');
    
    echo "</br>Available Products for Electricity:</br>";
    foreach ($products['products'] as $id => $product) {
        echo "ID: $id - {$product['name']}</br>";
    }
    
    // Example electricity bill payment
    // $transaction = $api->executeTransaction([
    //     'operator' => $operatorId,
    //     'accountId' => '04280379696', // Customer's meter number
    //     'amount' => 1000, // Amount in NGN
    //     'userReference' => 'ELEC' . time(),
    //     'extraParameters' => [
    //         'accountType' => 'PREPAID' // or 'POSTPAID' depending on customer
    //     ]
    // ]);
    
    // echo "</br>Electricity Payment Successful!</br>";
    // echo "Transaction ID: {$transaction['id']}</br>";
    // echo "Operator Reference: {$transaction['operator']['reference']}</br>";
    
} catch (Exception $e) {
    echo "Error processing electricity payment: " . $e->getMessage();
    error_log("Electricity Payment Error: " . $e->getMessage());
}