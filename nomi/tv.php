<?php
// ARTX API Configuration
$apiEndpoint = 'https://artx.sochitel.com/staging.php';
$username = 'testUser1';
$password = 'Test123!';

// Generate unique salt
function generateSalt() {
    return bin2hex(random_bytes(20));
}

// Calculate password hash
function calculateHash($password, $salt) {
    return sha1($salt . sha1($password));
}

// Send API request
function sendRequest($payload) {
    global $apiEndpoint;
    $ch = curl_init($apiEndpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// ===== 1. Get TV Subscription Operators =====
$salt = generateSalt();

$payload = [
    'auth' => [
        'username' => $username,
        'salt' => $salt,
        'password' => calculateHash($password, $salt),
        'signature' => ''
    ],
    'version' => 5,
    'command' => 'getOperators',
    'productType' => 7,
    'country' => 'NG'
];

$operators = sendRequest($payload);

if ($operators['status']['type'] !== 0) {
    die("Error fetching operators: " . $operators['status']['name']);
}

// Display available TV operators
echo "=== Available TV Operators ===\n";
foreach ($operators['result'] as $operator) {
    echo "ID: {$operator['id']} - {$operator['name']} ({$operator['currency']})\n";
}

// ===== 2. Select Operator (Use a valid operator ID from the above list) =====
$operatorId = 605; // example: replace with valid ID from above output
$salt = generateSalt();

$payload = [
    'auth' => [
        'username' => $username,
        'salt' => $salt,
        'password' => calculateHash($password, $salt),
        'signature' => ''
    ],
    'version' => 5,
    'command' => 'getOperatorProducts',
    'operator' => (int)$operatorId
];

$products = sendRequest($payload);

if ($products['status']['type'] !== 0) {
    echo "Error fetching products: " . $products['status']['name'] . "\n";
    print_r($products);
    die();
}

// Display available packages
echo "\n=== Available TV Packages ===\n";
foreach ($products['result']['products'] as $productId => $product) {
    echo "ID: $productId - {$product['name']} ({$product['price']['user']} {$products['result']['currency']['user']})\n";
    
    if (!empty($product['extraParameters'])) {
        echo "  Required Fields:\n";
        foreach ($product['extraParameters'] as $param => $details) {
            echo "  - {$details['name']}: " . ($details['mandatory'] ? "Required" : "Optional") . "\n";
            if (!empty($details['values'])) {
                echo "    Options: " . implode(", ", $details['values']) . "\n";
            }
        }
    }
}

// ===== 3. Execute TV Subscription Payment =====
$productId = 7012; // Example product ID
$smartcardNumber = '123456789012'; // Customer's smartcard number
$customerName = 'John Doe'; // Optional
$amount = 15000; // Amount in NGN
$salt = generateSalt();

$payload = [
    'auth' => [
        'username' => $username,
        'salt' => $salt,
        'password' => calculateHash($password, $salt),
        'signature' => ''
    ],
    'version' => 5,
    'command' => 'execTransaction',
    'operator' => (int)$operatorId,
    'accountId' => $smartcardNumber,
    'amountOperator' => $amount,
    'productId' => $productId,
    'userReference' => 'DSTV_' . time(),
    'extraParameters' => [
        'customerName' => $customerName,
        'bouquet' => 'Premium' // Example parameter
    ]
];

echo "\n=== Processing TV Subscription ===\n";
$transaction = sendRequest($payload);

switch ($transaction['status']['type']) {
    case 0:
        echo "✅ Payment Successful!\n";
        echo "Transaction ID: {$transaction['result']['id']}\n";
        echo "Operator Reference: {$transaction['result']['operator']['reference']}\n";
        if (isset($transaction['result']['instructions'])) {
            echo "Instructions: {$transaction['result']['instructions']}\n";
        }
        break;

    case 1:
        echo "🟡 Payment Pending\n";
        echo "Transaction ID: {$transaction['result']['id']}\n";
        echo "Check status later with getTransaction command\n";
        break;

    case 2:
        echo "🔴 Payment Failed: {$transaction['status']['name']}\n";
        if (isset($transaction['result']['alternativeProducts'])) {
            echo "Suggested Products: " . implode(", ", $transaction['result']['alternativeProducts']) . "\n";
        }
        break;
}

if ($transaction['status']['type'] !== 0) {
    echo "\nDebug Information:\n";
    print_r($transaction);
}
?>
