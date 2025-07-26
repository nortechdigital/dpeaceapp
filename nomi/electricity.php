<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// ARTX API Configuration
$apiEndpoint = 'https://artxh1.sochitel.com/api.php';
$username = 'dpeaceapp.api.ngn'; 
$password = 'login@DPeaceAdmin1234'; 

// Generate a unique salt for each request
function generateSalt() {
    return bin2hex(random_bytes(20)); // 40-character random string
}

// Calculate the password hash (SHA1)
function calculateHash($password, $salt) {
    $sha1Password = sha1($password);
    return sha1($salt . $sha1Password);
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

// --- Step 1: Get Electricity Operators (Bill Payment = productType 3) ---
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
    'productType' => 3, // Bill Payment
    'country' => 'NG'   // Nigeria (optional)
];
// $operators = sendRequest($payload);
$operators = sendRequest($payload);

// print_r($operators);die;


// Check for errors
if ($operators['status']['type'] !== 0) {
    die("Failed to fetch operators: " . $operators['status']['name']);
}

// Find Nigeria Eko Electricity (Operator ID 536)
$newOperator = null;
foreach ($operators['result'] as $operator) {
    if ($operator['id'] == '536') { // Nigeria Eko Electricity
        $newOperator = $operator;
        break;
    }
}


if (!$newOperator) {
    die("Nigeria Kaduna Electricity operator not found.");
}
// print_r($newOperator);
// --- Step 2: Get Products for Eko Electricity ---
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
    'operator' => '536' // Eko Electricity
];
$products = sendRequest($payload);
// print_r($products);die;

if ($products['status']['type'] !== 0) {
    die("Failed to fetch products: " . $products['status']['name']);
}

// --- Step 3: Validate Customer Account (Optional) ---
$accountId = '101150287452'; // Replace with customer's meter number
$salt = generateSalt();
$payload = [
    'auth' => [
        'username' => $username,
        'salt' => $salt,
        'password' => calculateHash($password, $salt),
        'signature' => ''
    ],
    'version' => 5,
    'command' => 'lookupAccount',
    'accountId' => $accountId,
    'productId' => '4268' 
];
$account = sendRequest($payload);
// print_r($payload);

if ($account['status']['type'] !== 0) {
    die("Account validation failed: " . $account['status']['name']);
}
// print_r($account);
// --- Step 4: Execute Bill Payment ---
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
    'operator' => '672', // Eko Electricity
    'accountId' => $accountId,
    'amount' => 1000, // Amount in user's currency (NGN for testUser1)
    'userReference' => 'BILL_' . time(), // Unique reference
    'productId' => '6094', // Product ID from getOperatorProducts
    'extraParameters' => [
        'accountType' => 'OFFLINE_PREPAID' // Example parameter
    ]
];
$transaction = sendRequest($payload);
// print_r($transaction);die;

// Check transaction status
if ($transaction['status']['type'] === 0) {
    echo "Payment successful! Transaction ID: " . $transaction['result']['id'];
    echo "\nNew Balance: " . $transaction['result']['balance']['final'] . " NGN";
} elseif ($transaction['status']['type'] === 1) {
    echo "Payment pending. Check later with:\n";
    echo "Transaction ID: " . $transaction['result']['id'];
} else {
    echo "Payment failed: " . $transaction['status']['name'];
}

?>