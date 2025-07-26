<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

// $name = $_SESSION['firstname'] .' '. $_SESSION['lastname'];
// $email = $_SESSION['email'];


$config = './config.php';
require __DIR__ . '/vendor/autoload.php';


use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

class CustomRsa {
    private $opayPublicKey;
    private $merchantPrivateKey;

    public function __construct($opayPublicKey, $merchantPrivateKey) {
        $this->opayPublicKey = $opayPublicKey;
        $this->merchantPrivateKey = $merchantPrivateKey;
    }

    public function encrypt($data, $timestamp) {
        if (!$data || !$timestamp) return "";

        $publicKey = PublicKeyLoader::load($this->opayPublicKey)
            ->withHash('sha256')
            ->withPadding(RSA::ENCRYPTION_PKCS1);

        $jsonData = json_encode($this->traverseData($data));
        
        // Split data into chunks of 100 bytes (leaving room for padding)
        $chunks = str_split($jsonData, 100);
        $encryptedChunks = [];
        
        foreach ($chunks as $chunk) {
            $encryptedChunks[] = $publicKey->encrypt($chunk);
        }
        
        $encrypted = implode('', $encryptedChunks);
        $sign = $this->setSign(base64_encode($encrypted) . $timestamp);

        return [
            'paramContent' => base64_encode($encrypted),
            'sign' => $sign
        ];
    }

    public function decrypt($data) {
        $privateKey = PublicKeyLoader::load($this->merchantPrivateKey)
            ->withHash('sha256')
            ->withPadding(RSA::ENCRYPTION_PKCS1);

        // Get the key size in bytes (1024-bit = 128 bytes)
        $keySize = $privateKey->getLength() / 8;
        $encryptedData = base64_decode($data['data']);
        
        // Split into chunks of key size
        $chunks = str_split($encryptedData, $keySize);
        $decryptedChunks = [];
        
        foreach ($chunks as $chunk) {
            $decryptedChunks[] = $privateKey->decrypt($chunk);
        }
        
        $decrypted = implode('', $decryptedChunks);
        $responseData = json_decode($decrypted, true);
        $sign = $data['sign'];
        unset($data['sign']);

        return [
            'verify' => $this->verifySign($sign, $this->traverseData($data)),
            'data' => $responseData
        ];
    }

    private function setSign($inputString) {
        $privateKey = PublicKeyLoader::load($this->merchantPrivateKey)
            ->withHash('sha256')
            ->withPadding(RSA::SIGNATURE_PKCS1);

        $signature = $privateKey->sign($inputString);
        return base64_encode($signature);
    }

    private function verifySign($sign, $data) {
        $publicKey = PublicKeyLoader::load($this->opayPublicKey)
            ->withHash('sha256')
            ->withPadding(RSA::SIGNATURE_PKCS1);

        $mapSplicing = '';
        ksort($data);
        foreach ($data as $k => $v) {
            if ($mapSplicing) {
                $mapSplicing .= '&';
            }
            $mapSplicing .= "$k=$v";
        }

        return $publicKey->verify($mapSplicing, base64_decode($sign));
    }

    private function traverseData($data) {
        if (is_array($data)) {
            if (array_keys($data) !== range(0, count($data) - 1)) {
                // Associative array
                $result = [];
                ksort($data);
                foreach ($data as $key => $value) {
                    $result[$key] = $this->traverseData($value);
                }
                return $result;
            } else {
                // Indexed array
                sort($data);
                return array_map([$this, 'traverseData'], $data);
            }
        }
        return $data;
    }
}

// Test case
function testOpayIntegration() {
    // Replace with actual keys
    $opayPublicKey = file_get_contents(__DIR__ . '/opay_public_key.pem');

    $merchantPrivateKey = file_get_contents(__DIR__ . '/merchant_private_key.pem');
    
    $config = require $GLOBALS['config'];

    $interfaceParameters = [
        'opayMerchantId' => $config['business_id'],
        'depositCodes' => ['6129188935'],
        'pageNo' => 1,
        'pageSize' => 5
        
    ];

    $crsa = new CustomRsa($opayPublicKey, $merchantPrivateKey);
    $timestamp = time() * 1000; // Milliseconds
    $encryptData = $crsa->encrypt($interfaceParameters, $timestamp);

    echo "Encrypt Data: " . json_encode($encryptData, JSON_PRETTY_PRINT) . "\n";

    $client = new \GuzzleHttp\Client();
    try {
        $response = $client->post('https://payapi.opayweb.com/api/v2/third/depositcode/queryStaticDepositCodeTransList', [
            'headers' => [
                'Content-Type' => 'application/json',
                'version' => 'V1.0.1',
                'bodyFormat' => 'JSON',
                'clientAuthKey' => $config['client_auth_key'], // Replace with actual key
                'timestamp' => $timestamp
            ],
            'json' => $encryptData
        ]);

        $responseData = json_decode($response->getBody(), true);
        "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";

        if ($responseData['code'] == '00000') {
            $decrypted = $crsa->decrypt($responseData);

            // Extract required fields
            $list = $decrypted['data']['data']['list'];
            foreach ($list as $item) {
                $depositCode = $item['depositCode'];
                $orderNo = $item['orderNo'];
                $depositAmount = $item['depositAmount'];
                $transactionId = $item['transactionId'];
                $dateTime = $item['formatDateTime'];
                $accountName = $item['payAccountName'];
                $status = $item['orderStatus'];

                // Display extracted values
                echo "Deposit Code: $depositCode<br>";
                echo "Order Number: $orderNo<br>";
                echo "Deposit Amount: $depositAmount<br>";
                echo "Transaction ID: $transactionId<br>";
                echo "Date Time: $dateTime<br>";
                echo "Account Name: $accountName<br>";
                echo "Status: $status<br>";

                // Calculate wallet balance and service charge
                $wallet_balance = 0;
                $service_charge = 0;

                if ($depositAmount <= 7250) {
                    $service_charge = 50;
                    $wallet_balance = $depositAmount - $service_charge;
                } else {
                    $percentage_charge = 0.007 * $depositAmount;
                    $service_charge = ($percentage_charge < 300) ? $percentage_charge : 300;
                    $wallet_balance = $depositAmount - $service_charge;
                }

                // Update wallet and transaction records
                // $userId = $user['id'];
                // echo $fullname = ucword($accountName);
                // $email = $_SESSION['email'];
                // $phoneNumber = $user['phone'];
                $type = 'Wallet topup';
                $details = 'Wallet Funding';
                echo $product_description = "Wallet funding of N$depositAmount via Opay Digital Wallet with service charge of N$service_charge was successful. Wallet credited with N$wallet_balance.";die;

            
                // Check wallet balance
                $stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Wallet not found for user']);
                    return;
                }

                $row = $result->fetch_assoc();
                $balance = $row['balance'];

                if ($status === 'SUCCESS') {
                    // Update wallet balance
                    $stmt = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
                    $stmt->bind_param("di", $wallet_balance, $userId);
                    $stmt->execute();

                    // Insert transaction record
                    $profit = 0; // Initialize profit to 0
                    $insert_sql = "INSERT INTO transactions (user_id, fullname, phone_number, product_description, amount, status, type, detail, transaction_ref, profit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insert_sql);
                    $stmt->bind_param("isssdssssi", $userId, $fullname, $phoneNumber, $product_description, $wallet_balance, $status, $type, $details, $transactionId, $profit);
                    $stmt->execute();
                }
            }
        } else {
            throw new Exception($responseData['message']);
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

testOpayIntegration();