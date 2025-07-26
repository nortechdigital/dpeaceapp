<?php
    include_once './_/conn.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: ./?page=login");
        exit;
    }
    $user_id = $_SESSION['user_id'];

    // Fetch user status
    $query = "SELECT status FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($status);
    $stmt->fetch();
    $stmt->close();

    // Fetch wallet balance
    $query = "SELECT balance FROM wallets WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($balance);
    $stmt->fetch();
    $stmt->close();

    // Fetch all virtual accounts for this user
    $accounts = [];
    $query = "SELECT * FROM wallet_accounts WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $accounts[] = $row;
    }
    $stmt->close();
?>

<?php

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    $config = './config.php';
    require './vendor/autoload.php';

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
        $opayPublicKey = file_get_contents('./opay_public_key.pem');

        $merchantPrivateKey = file_get_contents('./merchant_private_key.pem');
        
        $config = require $GLOBALS['config'];

        $interfaceParameters = [
            'opayMerchantId' => $config['business_id'],
            'name' => $_SESSION['firstname'] . ' ' . $_SESSION['lastname'],
            'email' => $_SESSION['email'],
            'accountType' => 'Merchant',
            'sendPassWordFlag' => 'N'
        ];

        $crsa = new CustomRsa($opayPublicKey, $merchantPrivateKey);
        $timestamp = time() * 1000; // Milliseconds
        $encryptData = $crsa->encrypt($interfaceParameters, $timestamp);

        // echo "Encrypt Data: " . json_encode($encryptData, JSON_PRETTY_PRINT) . "\n";

        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->post('https://payapi.opayweb.com/api/v2/third/depositcode/generateStaticDepositCode', [
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
            // echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";

            if ($responseData['code'] == '00000') {
    $decrypted = $crsa->decrypt($responseData);
    $depositCode = $decrypted['data']['data']['depositCode'];
    $userId = $_SESSION['user_id'];
    $conn = $GLOBALS['conn'];

    // Insert into digital_wallets
    $stmt = $conn->prepare("INSERT INTO digital_wallets (user_id, deposit_code) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $depositCode);

    if ($stmt->execute()) {
        echo "Deposit code saved successfully.\n";

        // Update users table
        $updateStmt = $conn->prepare("UPDATE users SET deposit_code = ? WHERE id = ?");
        $updateStmt->bind_param("si", $depositCode, $userId);

        if ($updateStmt->execute()) {
            echo "Deposit code updated successfully in users table.\n";
        } else {
            echo "Error updating deposit code in users table: " . $updateStmt->error . "\n";
        }

        header('Location: ./?page=wallet');
        exit;
                } else {
                    throw new Exception("Error saving deposit code: " . $conn->error);
                }
            } else {
                throw new Exception($responseData['message']);
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

    // testOpayIntegration();
?>

<?php
    $sql = "SELECT balance FROM wallets WHERE user_id = " . $_SESSION['user_id'];
    $rs = $conn->query($sql);
    if ($rs && $rs->num_rows > 0) {
        $balance = $rs->fetch_assoc();
        $balance = $balance['balance'];
    } else {
        $balance = 0.00;
    }

    $sql = "SELECT * FROM digital_wallets WHERE user_id = " . $_SESSION['user_id'];
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $wallet = $result->fetch_assoc();
        $depositCode = $wallet['deposit_code'];
    } else {
    	$depositCode = '';
        testOpayIntegration(); // Call the function to generate deposit code if no wallet exists
    }
?>

<div class="row">
    <div class="col-lg-2">
        <?php include "./inc/sidebar.php"; ?>
    </div>
    <div class="col-lg-10">
        <div class="container py-2">
            <h2 class="text-center bg-primary text-light h5">WALLET</h2>
            <div class="row py-3">
                <div class="col-md-8 offset-md-2">
                    <div class="card shadow p-4">
                        <h4>Current Balance: </span><span class="text-primary h2">&#8358;<?php echo number_format($balance, 2); ?></span></h4>
                        
                        <hr>
                        <h6>Virtual Account Details</h6>
                        <ul class="list-group">
                                <li class="list-group-item">
                                    <strong>Bank: Opay Digital Wallet <br>Account Number: <?= $depositCode ?></strong>
                                    <br>
                                    <strong>Account Name: <?= $_SESSION['firstname'] . ' ' . $_SESSION['lastname'] ?></strong>
                                </li>
                        </ul>
                        <hr>
                            <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#fundWalletModal">
                                Fund Wallet
                            </button>

                            <!-- Modal -->
                            <div class="modal fade" id="fundWalletModal" tabindex="-1" aria-labelledby="fundWalletModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="fundWalletModalLabel">Fund Wallet</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <h5>Top up your wallet:</h5>
                                            <strong class="h5 text-center">Bank: <span class="text-primary">Opay Digital Wallet</span></strong> <br>
                                            <strong class="h5 text-center">Account Number: <span class="text-primary"><?php echo htmlspecialchars($depositCode); ?></span></strong> <br>
                                            <strong class="h5 text-center">Account Name: <span class="text-primary"><?php echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']); ?></span></strong>                                            
                                            <hr>
                                            <p class=""><span class="h5 text-danger">Note:</span>Wallet Top-up Fees:
                                            <ul>
                                                <li>Under &#8358;7,250: &#8358;50 flat fee.</li>
                                                <li>&#8358;7,251 and above: 0.7% fee, capped at N300.</li>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>