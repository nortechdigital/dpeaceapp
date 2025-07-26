<?php

include("./conn.php");

session_start();

$config = './config.php';
require __DIR__ . '/vendor/autoload.php';

$his = fopen("his.txt", "w");
fwrite($his, date('Y-m-d H:i:s A'));
fclose($his);

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
	global $conn;

    $interfaceParameters = [
        'opayMerchantId' => $config['business_id'],
        'pageNo' => 1,
        'pageSize' => 10
    ];

    $crsa = new CustomRsa($opayPublicKey, $merchantPrivateKey);
    $timestamp = time() * 1000; // Milliseconds
    $encryptData = $crsa->encrypt($interfaceParameters, $timestamp);

    // echo "Encrypt Data: " . json_encode($encryptData, JSON_PRETTY_PRINT) . "\n";

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

               

                // Calculate wallet balance and service charge
            	$wallet_balance = 0;
                $service_charge = 0;
                $service_charge1 = 0;
                

                if ($depositAmount <= 6300) {
                    $service_charge = 50;
                    $service_charge1 = 0.003 * $depositAmount;
                    $wallet_balance = $depositAmount - $service_charge;
                } else {
                    $percentage_charge = 0.008 * $depositAmount;
                    $service_charge = ($percentage_charge < 300) ? $percentage_charge : 300;
                    $percentage_charge1 = 0.003 * $depositAmount;
                    $service_charge1 = ($percentage_charge1 < 100) ? $percentage_charge1 : 100;
                    $wallet_balance = $depositAmount - $service_charge;
                }

                $wallet_profit = $service_charge - $service_charge1;
                

                // Update wallet and transaction records
                $stmt = $conn->prepare("SELECT * FROM users WHERE deposit_code = ?");
				$stmt->bind_param("s", $depositCode);
				$stmt->execute();
				$userResult = $stmt->get_result();

				if ($userResult->num_rows === 0) {
   				http_response_code(404);
    			// echo json_encode(['error' => 'User not found for deposit code']);
    			return;
				}

				$user = $userResult->fetch_assoc();
				
                $userId = $user['id'];
                $fullname = $user['firstname'] . ' ' . $user['lastname'];
                $email = $_SESSION['email'];
                $phoneNumber = $user['phone'];
                $type = 'Wallet topup';
                $details = 'Wallet Funding';
                $product_description = "Wallet funding of N$depositAmount via Opay Digital Wallet with service charge of N$service_charge was successful. Wallet credited with N$wallet_balance.";

            
                // Check wallet balance
                $stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    http_response_code(404);
                    // echo json_encode(['error' => 'Wallet not found for user']);
                    return;
                }

                $row = $result->fetch_assoc();
                $balance = $row['balance'];
            	$new_balance = $balance + $wallet_balance;
            
            	// Check for duplicate transaction
				$stmt = $conn->prepare("SELECT id FROM transactions WHERE transaction_ref = ?");
				$stmt->bind_param("s", $transactionId);
				$stmt->execute();
				$txCheckResult = $stmt->get_result();

				if ($txCheckResult->num_rows > 0) {
    				// echo "Duplicate transaction detected. Skipping wallet top-up.<br>";
    				continue; // Skip this transaction
				}

                if ($status === 'SUCCESS') {
                    // Update wallet balance
                    $stmt = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
                    $stmt->bind_param("di", $wallet_balance, $userId);
                    $stmt->execute();

                    // Insert transaction record
                    $profit = 0;
                    $insert_sql = "INSERT INTO transactions (user_id, fullname, phone_number, product_description, amount, status, type, detail, transaction_ref, profit, order_no, wallet_profit, current_balance, new_balance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insert_sql);
                    $stmt->bind_param("isssdssssissss", $userId, $fullname, $phoneNumber, $product_description, $wallet_balance, $status, $type, $details, $transactionId, $profit, $orderNo, $wallet_profit, $balance, $new_balance);
                    $stmt->execute();
                }
            }

            $user_id = $_SESSION['user_id']; $bal = 0;
            $sql = "SELECT balance FROM wallets WHERE user_id = $user_id";
            $rs = $conn->query($sql);
            if ($rs && $rs->num_rows > 0) {
                while ($row = $rs->fetch_assoc()) {
                    $bal = $row['balance'];
                }
            }
            echo "₦" . number_format($bal, 2);

        } else {
            // throw new Exception($responseData['message']);
            $user_id = $_SESSION['user_id']; $bal = 0;
            $sql = "SELECT balance FROM wallets WHERE user_id = $user_id";
            $rs = $conn->query($sql);
            if ($rs && $rs->num_rows > 0) {
                while ($row = $rs->fetch_assoc()) {
                    $bal = $row['balance'];
                }
            }
            echo "₦" . number_format($bal, 2);
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

testOpayIntegration();