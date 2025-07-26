<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

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
        'name' => 'VPPX01',
        'email' => 'vppx01@gmail.com',
        'accountType' => 'Merchant',
        'sendPassWordFlag' => 'Y'
    ];

    $crsa = new CustomRsa($opayPublicKey, $merchantPrivateKey);
    $timestamp = time() * 1000; // Milliseconds
    $encryptData = $crsa->encrypt($interfaceParameters, $timestamp);

    echo "Encrypt Data: " . json_encode($encryptData, JSON_PRETTY_PRINT) . "\n";

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
        echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";

        if ($responseData['code'] == '00000') {
            $decrypted = $crsa->decrypt($responseData);
            // Process the decrypted data
            print_r($decrypted);
        } else {
            throw new Exception($responseData['message']);
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

testOpayIntegration();

// Encrypt Data: { "paramContent": "DlcYllOg9puPiu6O4mXNfJTyoGi3B9qy4Fr7IX6mYtucmt9PpHvOYiefGqUopMZ9Wq7ekt4yRfbeVF5tjDthEsG9S03ZmbR059HMPoxPWVdZVboth98fFRRUxYo\/TphWW\/Ga6JWh67U3N9\/Xy7bpf8lJXpz2wtifWWhcQAAMi44NBOY3VN\/myrZUjr4qthAFW1thhULjIpZbh\/qKemhyvfnWTHdSQexDF1qUDxuAbal6YSXwFzqvX+VP9p6Nj14mTW5o4CLVwZpkn+kZBget7gFYMJxboMTPg06Wk6SafKVeACgXACrOVfz4vMHgUez7Ro4xAjzIx8A8aR3ORU8ABg==", "sign": "sXLV5rq7XIBugN7FkqGQdU4vEf2lyG6APQaJoXyvv2ZTyfi3wFwOSCs2eugB1+NVvH3O8lBd2hKj7AMK6HfMi6tU1eGLHsSwrnEPUYnLDYPJNyG7jwTUd5eYOamnvM+2o3AQBMe3H1bo1YHTzVqowpLFvV58je9AtMiX8q5Nee4=" } Response: { "code": "00000", "message": "SUCCESS", "timestamp": "1749471884544", "sign": "CzdTtdqsuBJaFxjsTEXr6ndhHFb9Xb0CmTMwFqLFrZV+cK9LjhGXgwO8s9c133uNgDkJU1f8Fujr0\/fX4Xs42QXZltiTDmAzw8sGiaWLlAUp8YPxvNVtkS4wtLgSZbdVYfrboq\/N9MToV\/XGVCn8g0hE9HEZR\/Rx3a8dwigDNEo=", "data": "EnSinTACLWoBShspGLCTkViYcFKpL6t1\/lYGHEX3C6Z0HTA3XzcVIss62uqwLanrga7S3hehfGRsjYfC97346OV5KrPYR1SUVczHms7FdrRNMQIzcvYL+kFm4I58yFldJkQaWpadYSpLEjUOi5F6ksMM3oh1IwEqZviRrzcUP4M4PMn\/hyrgz1eImWhlTpA2ixhsjNH2i77MWGUVWZ8BYVPW7vwW83CiiQdYLL3kOJWPT64KdLu9D4Vf01WL5Jh9cpQGxUZAbODOImIeItDtyaCOiDWbDAbPTSfUZYAOxRpOET1SrsgtrZGHbdtiJ1GCBVq4lij3DOFfk1zi34VJNw==" } Array ( [verify] => 1 [data] => Array ( [code] => 00000 [data] => Array ( [depositCode] => 6120834272 [accountType] => Merchant [emailOrPhone] => vppx01@gmail.com [name] => VPPX01 ) [message] => SUCCESSFUL ) )