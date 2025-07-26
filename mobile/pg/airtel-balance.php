<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$base_url = "https://172.24.4.21:4443/pretups/C2SReceiver?REQUEST_GATEWAY_CODE=DPEA&REQUEST_GATEWAY_TYPE=EXTGW&LOGIN=pretups&PASSWORD=908cff9930023413c4eff8e45acaa7e8&SOURCE_TYPE=EXTGW&SERVICE_PORT=191";

$transaction_date = date('d/m/Y H:i:s');




$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $base_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => '<?xml version="1.0"?>
<COMMAND>
<TYPE>EXUSRBALREQ</TYPE>
<DATE>' . $transaction_date . '</DATE>
<EXTNWCODE>NG</EXTNWCODE>
<MSISDN>9010010731</MSISDN>
<PIN>3690</PIN>
<LOGINID></LOGINID>
<PASSWORD></PASSWORD>
<EXTCODE></EXTCODE>
<EXTREFNUM></EXTREFNUM>
</COMMAND>',
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/xml',
    ],
]);

$response = curl_exec($curl);

if (curl_errno($curl)) {
    echo 'Curl error: ' . curl_error($curl);
} else {
    echo 'Response: ' . $response;
}

curl_close($curl);
?>