<?php
session_start();
require "../conn.php";

// // 1. Verify session and request method
// if (!isset($_SESSION['user_id'])) {
//     header('Content-Type: application/json');
//     die(json_encode(['status' => 'error', 'message' => 'Session expired. Please login again.']));
// }

2. Get payment details from POST data
$amount = $_POST['amount'] ?? null;
$cust_reference = $_POST['cust_reference'] ?? null;

if (empty($amount) || empty($cust_reference)) {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Amount and customer reference are required']));
}

$amount = rand(100, 10000); // Random amount between 100 and 10000
$cust_reference = "66770"; // Unique customer reference

// 3. Construct XML request
$payment_log_id = uniqid("LOG_");
$payment_date = date('Y-m-d H:i:s');
$xml_request = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<PaymentNotificationRequest>
    <Payments>
        <Payment>
            <PaymentLogId>{$payment_log_id}</PaymentLogId>
            <CustReference>{$cust_reference}</CustReference>
            <Amount>{$amount}</Amount>
            <PaymentMethod>WEB</PaymentMethod>
            <PaymentDate>{$payment_date}</PaymentDate>
            <IsReversal>false</IsReversal>
            <PaymentItems>
                <PaymentItem>
                    <ItemName>Service</ItemName>
                    <ItemCode>Service</ItemCode>
                    <ItemAmount>{$amount}</ItemAmount>
                </PaymentItem>
            </PaymentItems>
        </Payment>
    </Payments>
</PaymentNotificationRequest>
XML;

// 4. Send XML request to partner endpoint
$endpoint = "http://swiftng.com:3000/ISWPayment.ashx";
$username = "PeaceAppTest";
$password = "peaceapptest";
$partner = "PeaceApp";
// http://.swiftng.com:3000/ISWPayment.ashx?Username=PeaceAppTest&Password=peaceapptest&Partner=PeaceApp

$url = "{$endpoint}?Username={$username}&Password={$password}&Partner={$partner}";
$headers = [
    "Content-Type: application/xml",
    "Content-Length: " . strlen($xml_request)
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $xml_request,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// 5. Process XML response
if ($http_code === 200 && $response) {
    $response_xml = simplexml_load_string($response);
    $status = (string)$response_xml->Payments->Payment->Status ?? null;
    $status_desc = (string)$response_xml->Payments->Payment->StatusDesc ?? null;

    if ($status === "0") {
        // Payment successful
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Payment successful',
            'data' => [
                'paymentLogId' => $payment_log_id,
                'cust_reference' => $cust_reference,
                'amount' => $amount,
                'status' => $status,
                'status_desc' => $status_desc
            ]
        ]);
    } else {
        // Payment failed
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $status_desc,
            'http_code' => $http_code,
            'curl_error' => $curl_error
        ]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to connect to payment partner',
        'http_code' => $http_code,
        'curl_error' => $curl_error
    ]);
}
?>
