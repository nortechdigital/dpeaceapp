
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class PalmPayClient
{
    private const API_BASE_URL = 'https://open-gw-daily.palmpay-inc.com';
    private const API_VERSION = 'V2';

    private string $merchantAppId = 'L231204055835021842101';
    private string $countryCode = 'NG';

    private string $merchantPrivateKey = <<<EOD
-----BEGIN PRIVATE KEY-----
MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCgalkoceeiBxnX
Y9TszucuLxCeRWAHMg90VRc5NjY4ar/LJQDhpLk3Ty57u8nEf74ADUx29yTiefVx
khGgATBuAOt3e0PHpbPP6/yOj7/q+PxOR5J481y2ExGCLnJPbQZbWo6hQus33sjQ
p1EPqOlYWeylDVjpz2d87QWmzShDLYYHZV3x78AQg7hDvBOIpD1yUjbqz1aQJFyV
hHHOLh/fa+WZlArRiJ2dBRkSp1YeHCFFYkpQ48kvQJ9bm0rH13s9+Mz0aOSb9uEl
JQcOOv13E0xQZlBKLBZREbjN6ChEjUELojCq2aCmwHKoruKiA1QnZMRsKjCyTzqI
iC2jvvwnAgMBAAECggEADNuZW+GNZHJXQulwlq6a3xvMpDMBWHJNxNBRNTfALtN3
ngvQP0XZxrIlEqvhp0tp6k0mlN6IaVLHNpwzp3SQ8jBGr2QE8cq5V/AdZTvmcSoV
5xxbhDBVfQ6YN6wLY4xklwvyJMDdY7QKupa+q5epZHiIvE4Ok2cZb2z8J/uHv6KU
jt6li/Nzoi055Rw2ogZe9cVDClG7i5L8WUSeFjE2IjbOI955IhIsJjcKTaIivkif
VZ+vONqpOUYJEDKebHaXTHlzNFXyg3bIKPe5HPbir8VjtIe1Zq21bGkwV4QDmPpZ
8WWXyDaiwNhOGo+VwZb/jWweGzgry5s0V1g9Q90amQKBgQDu94FA03e3Yh/LkLma
BFHXsErOD4wSBV29QPRyUmbhyOa3ADNOoaR2YRRRlKmS+zOKJHDyxuR6IPYD6up1
sYKF2T0mMKoUhqcp6+j/iPw1rbzTSbwJwJOwCE0CdEQPyTjDtDAL4egKYpShYXmO
2Qlwk42FtFquG6XfVOGqPv2WMwKBgQCr2X5vKHWeEbeY9S9rhMKh/H++QkwxU2/U
rob3A1eHN5CeBWQx82W59BjpLuxYiNemReHaHr1FNvjEK6Zr9sBhSZmzDlmNAVDn
Vhc7MZ4fCYRO7HmLejCjx+K+mcH2zpH+8A4JBBYjntBMJKvIyXm2IwmcOOTB56hV
3Oycof8GPQKBgB5MCPokFXiNm0Re2/k39PxooINRm0upnIHjG1rnMZ4Mr5uiDd85
RTWxBzd0pq845AbuqddN+ie1yBslDIbRc5/us/8EinvBuq3o+Ah14KwZk+gh4BJI
dTELTGA0R3DM7UJ6tOC8yoOOjhOL3TKMN9MrEfVSsXCDltsi0t2X0OTbAoGAcF2S
ElS+M1EaX2VSUFdKfGiBjoIDF+2andJQZYtF3CA0615TGWYxCdnVwALyfyFbAFmJ
R/n5gBxlpL913fpF6FcbrLyhSVWm9NyR7B6RaXHrlT+CafTHgQ/d7wrSjPKc+7kz
NCn73+akBGWl/W/fqXxXeFKrIS68HwiJnhE+k3ECgYAaGCeGhz7geoZONXkPpDVo
OAmjlwHrUM5LOX0PsGUQbz40TDkm1V2daA/9hUb3XS+7FK8CmhKdjPliNNPd6oJh
8343d7eUGJ4P8OG3C0RWq7kjc+BeL3vfNBILcQSM17aqDtshYqRBfE+W7snD9boR
uSXOtxUZdKIo9qTkNLaAqA==
-----END PRIVATE KEY-----
EOD;

    private string $palmpayPublicKey = <<<EOD
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAoGpZKHHnogcZ12PU7M7n
Li8QnkVgBzIPdFUXOTY2OGq/yyUA4aS5N08ue7vJxH++AA1Mdvck4nn1cZIRoAEw
bgDrd3tDx6Wzz+v8jo+/6vj8TkeSePNcthMRgi5yT20GW1qOoULrN97I0KdRD6jp
WFnspQ1Y6c9nfO0Fps0oQy2GB2Vd8e/AEIO4Q7wTiKQ9clI26s9WkCRclYRxzi4f
32vlmZQK0YidnQUZEqdWHhwhRWJKUOPJL0CfW5tKx9d7PfjM9Gjkm/bhJSUHDjr9
dxNMUGZQSiwWURG4zegoRI1BC6IwqtmgpsByqK7iogNUJ2TEbCowsk86iIgto778
JwIDAQAB
-----END PUBLIC KEY-----
EOD;

    public function queryBiller(string $sceneCode = 'airtime'): array
    {
        $url = self::API_BASE_URL . '/api/v2/bill-payment/biller/query';
        $params = $this->buildParams(['sceneCode' => $sceneCode]);
        $headers = $this->buildHeaders($params);
        $payload = json_encode($params);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => $headers
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new RuntimeException('cURL Error: ' . curl_error($ch));
        }

        curl_close($ch);

        $result = json_decode($response, true);

        if ($result === null) {
            throw new RuntimeException("Invalid JSON response:\n$response");
        }

        if ($httpCode !== 200 || ($result['respCode'] ?? '') !== '00000000') {
            throw new RuntimeException("API Error [HTTP $httpCode]: " . json_encode($result));
        }

        return $result;
    }

    private function buildParams(array $customParams = []): array
    {
        return array_merge([
            'requestTime' => round(microtime(true) * 1000),
            'nonceStr'    => bin2hex(random_bytes(16)),
            'version'     => self::API_VERSION,
            'appId'       => $this->merchantAppId,
        ], $customParams);
    }

    private function buildHeaders(array $params): array
    {
        return [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $this->merchantAppId,
            'CountryCode: ' . $this->countryCode,
            'Signature: ' . $this->generateSignature($params),
            'Signature-Type: RSA',
            'Signature-Version: 1.0'
        ];
    }

    private function palmPayUrlEncode(string $value): string
    {
        return str_replace(
            ['%3A', '%2F', '%40', '%2B', '%3D', '%26', '%3F', '%25', '%23', '%24', '%2C', '%7E'],
            [':', '/', '@', '+', '=', '&', '?', '%', '#', '$', ',', '~'],
            rawurlencode($value)
        );
    }

    private function generateSignature(array $params): string
    {
        $filteredParams = array_filter($params, fn($value) => $value !== null && $value !== '');
        ksort($filteredParams);

        $stringToSign = '';
        foreach ($filteredParams as $key => $value) {
            $stringToSign .= $key . '=' . $this->palmPayUrlEncode($value) . '&';
        }
        $stringToSign = rtrim($stringToSign, '&');

        $privateKey = openssl_pkey_get_private($this->merchantPrivateKey);
        if (!$privateKey) {
            throw new RuntimeException('Unable to load private key');
        }

        $success = openssl_sign($stringToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (!$success) {
            throw new RuntimeException('Failed to sign the request');
        }

        return base64_encode($signature);
    }
}

// ===================
// Run the biller query
// ===================

try {
    $client = new PalmPayClient();
    $response = $client->queryBiller('airtime');

    echo "✅ PalmPay Billers for 'airtime':\n";
    foreach ($response['data'] as $biller) {
        echo "- {$biller['billerName']} ({$biller['billerId']}) | Status: " .
             ($biller['status'] ? 'Available' : 'Unavailable') .
             ", Min: {$biller['minAmount']}, Max: {$biller['maxAmount']}\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
