<?php
session_start();
include "../conn.php";

// BulkSMSNigeria API - SMS Sending Example
$apiToken = "rTcOxxK0b7qEToNXglxxaw5jtI7XzgYAghxPQy1FSFl85N9lz6a82hyebzAA"; // Replace with your actual API token

// API Endpoint
$url = "https://www.bulksmsnigeria.com/api/v2/sms";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = test_input($_POST['firstname']);
    $lastname = test_input($_POST['lastname']);
    $email = test_input($_POST['email']);
    $phone = test_input($_POST['phone']);
    $password = test_input($_POST['password']);
    $password = password_hash($password, PASSWORD_DEFAULT);
    $otp = rand(100000, 999999);
    $status = 0;

    // Store user data in session
    $_SESSION['pre_user'] = [
        'firstname' => $firstname,
        'lastname' => $lastname,
        'email' => $email,
        'phone' => $phone,
        'otp' => $otp,
        'status' => $status,
        'password' => $password
    ];

    // Send OTP to email
    $subject = 'DPeace App OTP Code';
    $message = "
    <html>
    <head>
        <style>
            .email-container {
                font-family: Arial, sans-serif;
                text-align: center;
                padding: 20px;
            }
            .logo {
                width: 150px;
                margin-bottom: 20px;
            }
            .otp-message {
                font-size: 16px;
                color: #333;
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <img src='https://dpeaceapp.com/img/dpeace-app.png' alt='DPeace Logo' class='logo'>
            <p class='otp-message'>Dear $firstname $lastname,</p>
            <p class='otp-message'>Your OTP code is <strong>$otp</strong>.</p>
            <p class='otp-message'>Welcome to DPeace App!</p>
        </div>
    </body>
    </html>";
    $headers = "From: no-reply@dpeaceapp.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    if (mail($email, $subject, $message, $headers)) {
        echo "OTP sent to email successfully.\n";
    } else {
        echo "Failed to send OTP to email. Please try again.\n";
    }
    header("Location: ../?page=verify_otp");
}

?>

?>

// <?php
// // SMS Data
// $smsData = [
//     "from" => "DPeaceApp",       // Sender ID (max 11 chars)
//     "to" => "$phone",      // Recipient number (with country code)
//     "body" => "Hello from DPeaceApp! Your OTP is $otp", // Message content
//     "api_token" => $apiToken,
//     "gateway" => "direct-refund", // Optional: delivery route
//     "append_sender" => "hosted",  // Optional: append sender ID
//     // "callback_url" => "https://yourdomain.com/sms-callback", // Optional: for delivery reports
//     // "customer_reference" => "TEST123" // Optional: your reference ID
// ];

// // Initialize cURL
// $curl = curl_init();

// curl_setopt_array($curl, [
//     CURLOPT_URL => $url,
//     CURLOPT_RETURNTRANSFER => true,
//     CURLOPT_ENCODING => '',
//     CURLOPT_MAXREDIRS => 10,
//     CURLOPT_TIMEOUT => 30,
//     CURLOPT_FOLLOWLOCATION => true,
//     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//     CURLOPT_CUSTOMREQUEST => 'POST',
//     CURLOPT_POSTFIELDS => json_encode($smsData),
//     CURLOPT_HTTPHEADER => [
//         'Accept: application/json',
//         'Content-Type: application/json'
//     ],
// ]);

// // Execute the request
// $response = curl_exec($curl);
// $err = curl_error($curl);
// curl_close($curl);

// // Handle response
// if ($err) {
//     echo "cURL Error: " . $err;
// } else {
//     $result = json_decode($response, true);
    
//     if (isset($result['data']['status']) && $result['data']['status'] == 'success') {
//         echo "SMS sent successfully!\n";
//         echo "Message ID: " . $result['data']['message_id'] . "\n";
//         echo "Cost: " . $result['data']['cost'] . " " . $result['data']['currency'] . "\n";
//     } elseif (isset($result['error']['message'])) {
//         echo "Error: " . $result['error']['message'] . "\n";
//     } else {
//         echo "Unexpected response:\n";
//         print_r($result);
//     }
// }
// ?>