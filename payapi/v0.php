<?php
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

        $encrypted = $publicKey->encrypt(json_encode($this->traverseData($data)));
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

        $decrypted = $privateKey->decrypt(base64_decode($data['data']));
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

    $interfaceParameters = [
        'headMerchantId' => '256622092286390',
        'merchantId' => '256622092286391',
        'outOrderNo' => '2334345345348734',
        'amount' => '2200.00',
        'currency' => 'NGN',
        'orderExpireTime' => 300,
        'productInfo' => json_encode([
            'filmName' => 'Avatar:The Way of Water',
            'filmTitle' => 'ticket title',
            'filmTicketNum' => 'film ticker number',
            'seatNum' => 'Seat number(multiple seats, concatenate)',
            'filmTicketAmount' => '100',
            'filmFeeAmount' => '5',
            'filmDate' => 'movie date 2024-05-12',
            'filmTime' => 'movie showtime, 19:00'
        ]),
        'isSplit' => 'N',
        'remark' => 'test film app',
        'sceneEnum' => 'COLLECTION_SDK',
        'subSceneEnum' => 'LOGISTIC',
        'sn' => '9210264890'
    ];

    $crsa = new CustomRsa($opayPublicKey, $merchantPrivateKey);
    $timestamp = time() * 1000; // Milliseconds
    $encryptData = $crsa->encrypt($interfaceParameters, $timestamp);

    echo "Encrypt Data: " . json_encode($encryptData, JSON_PRETTY_PRINT) . "\n";

    $client = new \GuzzleHttp\Client();
    try {
        $response = $client->post('https://payapi.opayweb.com/openApi/order/checkout/createOrder', [
            'headers' => [
                'Content-Type' => 'application/json',
                'version' => 'V1.0.1',
                'bodyFormat' => 'JSON',
                'clientAuthKey' => '9a2ca7297afd493a84d7a538a04e210m', // Replace with actual key
                'timestamp' => $timestamp
            ],
            'json' => $encryptData
        ]);

        $responseData = json_decode($response->getBody(), true);
        echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";

        if ($responseData['code'] == '00000') {
            $decrypted = $crsa->decrypt($responseData);
            echo "Verify sign: " . ($decrypted['verify'] ? 'true' : 'false') . "\n";
            echo "Decrypt response data: " . json_encode($decrypted['data'], JSON_PRETTY_PRINT) . "\n";
        } else {
            throw new Exception($responseData['message']);
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

testOpayIntegration();