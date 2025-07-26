<?php
// customer_validation.php
session_start();

// Define API credentials and endpoint
$api_url = "http://swiftng.com:3000/customer.aspx";
$username = "PeaceAppTest";
$password = "peaceapptest";
$partner = "PeaceApp";
$customer_id = "66770";


// Validate customer_id input
// $customer_id = $_GET['customer_id'] ?? null;
// if (empty($customer_id)) {
//     header('Content-Type: application/json');
//     die(json_encode(['status' => 'error', 'message' => 'Customer ID is required']));
// }

// Build the API URL with query parameters
$request_url = $api_url . "?Username=" . urlencode($username) . 
               "&Password=" . urlencode($password) . 
               "&Partner=" . urlencode($partner) . 
               "&customer_id=" . urlencode($customer_id);

// Initialize cURL
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $request_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Handle response
if ($http_code === 200 && $response) {
    // Parse the XML response
    $xml = simplexml_load_string($response);
    if ($xml && isset($xml->Customer)) {
        $customer = $xml->Customer;
        $status_code = (string) $customer->StatusCode;
        $status_description = (string) $customer->StatusDescription;

        if ($status_code === "0") {
            // Valid customer
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Valid customer',
                'data' => [
                    'customer_id' => (string) $customer->CustomerId,
                    'first_name' => (string) $customer->FirstName,
                    'last_name' => (string) $customer->LastName,
                    'status_description' => $status_description
                ]
            ]);
        } else {
            // Invalid customer
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => $status_description
            ]);
        }
    } else {
        // Invalid XML response
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid response format'
        ]);
    }
} else {
    // API request failed
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'API request failed',
        'http_code' => $http_code,
        'curl_error' => $curl_error
    ]);
}
?>
