<?php

class PretupsIntegration {
    private $baseUrl;
    private $gatewayCode;
    private $login;
    private $password;
    private $networkCode = 'NG'; // Nigeria

    public function __construct($ip, $port, $gatewayCode, $login, $password) {
        $this->baseUrl = "https://$ip:$port/pretups/C2SReceiver";
        $this->gatewayCode = $gatewayCode;
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * Make API request to PreTUPS
     */
    private function makeRequest($xmlRequest) {
        $url = $this->baseUrl . '?' . http_build_query([
            'REQUEST_GATEWAY_CODE' => $this->gatewayCode,
            'REQUEST_GATEWAY_TYPE' => 'EXTGW',
            'LOGIN' => $this->login,
            'PASSWORD' => $this->password,
            'SOURCE_TYPE' => 'EXTGW',
            'SERVICE_PORT' => 191
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/xml'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode != 200) {
            throw new Exception("API request failed with HTTP code: $httpCode");
        }

        return simplexml_load_string($response);
    }

    /**
     * Purchase airtime
     */
    public function purchaseAirtime($msisdn, $amount, $pin, $reference) {
        $xml = '<?xml version="1.0"?>
        <COMMAND>
            <TYPE>EXRCTRFREQ</TYPE>
            <DATE>' . date('d/m/Y H:i:s') . '</DATE>
            <EXTNWCODE>' . $this->networkCode . '</EXTNWCODE>
            <MSISDN>' . $msisdn . '</MSISDN>
            <PIN>' . $pin . '</PIN>
            <EXTREFNUM>' . $reference . '</EXTREFNUM>
            <AMOUNT>' . $amount . '</AMOUNT>
        </COMMAND>';

        return $this->makeRequest($xml);
    }

    /**
     * Purchase data bundle
     */
    public function purchaseData($msisdn, $amount, $pin, $reference, $subService) {
        $xml = '<?xml version="1.0"?>
        <COMMAND>
            <TYPE>VASSELLREQ</TYPE>
            <DATE>' . date('d/m/Y H:i:s') . '</DATE>
            <EXTNWCODE>' . $this->networkCode . '</EXTNWCODE>
            <MSISDN>' . $msisdn . '</MSISDN>
            <PIN>' . $pin . '</PIN>
            <EXTREFNUM>' . $reference . '</EXTREFNUM>
            <SUBSERVICE>' . $subService . '</SUBSERVICE>
            <AMT>' . $amount . '</AMT>
        </COMMAND>';

        return $this->makeRequest($xml);
    }

    /**
     * Check user balance
     */
    public function checkBalance($msisdn, $pin) {
        $xml = '<?xml version="1.0"?>
        <COMMAND>
            <TYPE>EXUSRBALREQ</TYPE>
            <DATE>' . date('d/m/Y H:i:s') . '</DATE>
            <EXTNWCODE>' . $this->networkCode . '</EXTNWCODE>
            <MSISDN>' . $msisdn . '</MSISDN>
            <PIN>' . $pin . '</PIN>
        </COMMAND>';

        return $this->makeRequest($xml);
    }

    /**
     * Check transaction status
     */
    public function checkTransactionStatus($msisdn, $pin, $reference, $txnId = '') {
        $xml = '<?xml version="1.0"?>
        <COMMAND>
            <TYPE>EXRCSTATREQ</TYPE>
            <DATE>' . date('d/m/Y H:i:s') . '</DATE>
            <EXTNWCODE>' . $this->networkCode . '</EXTNWCODE>
            <MSISDN>' . $msisdn . '</MSISDN>
            <PIN>' . $pin . '</PIN>
            <EXTREFNUM>' . $reference . '</EXTREFNUM>
            <TXNID>' . $txnId . '</TXNID>
        </COMMAND>';

        return $this->makeRequest($xml);
    }
}

// Example Usage:
try {
    // Initialize with your credentials
    $pretups = new PretupsIntegration(
        '172.24.4.21',  // IP
        4443,           // Port
        'DPEA', // YOUR_GATEWAY_CODE
        'pretups', // YOUR_LOGIN
        '908cff9930023413c4eff8e45acaa7e8' // YOUR_PASSWORD
    );

    // Purchase airtime
    $response = $pretups->purchaseAirtime(
        '8083563344',   // Recipient MSISDN
        500,            // Amount
        '1234',         // PIN
        'TX123456'      // Your reference number
    );

    // Check response status
    if ((int)$response->TXNSTATUS === 200) {
        echo "Airtime purchase successful. TXN ID: " . $response->TXNID;
    } else {
        echo "Airtime purchase failed. Status: " . $response->TXNSTATUS;
    }

    // Check balance
    $balanceResponse = $pretups->checkBalance('9028888383', '1234');
    echo "Current balance: " . $balanceResponse->RECORD->BALANCE;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}