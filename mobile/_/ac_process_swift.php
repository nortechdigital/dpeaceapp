<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$debug = fopen('debug', 'w');
fwrite($debug, date('H:i:s') . "\n");

session_start();
include "../conn.php";
include "../_/ac_config.php";
    
if (!isset($_SESSION['user_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'User session not found']);
  exit;
}

// Check wallet balance using prepared statement
$stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo json_encode(['status' => 'error', 'message' => 'User wallet not found']);
  exit;
}

$row = $result->fetch_assoc();
$balance = $row['balance'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Array ( [cust_reference] => 66770 [hidden_amount] => 1800.00 [discounted_price] => 1620.00 )
  $cust_reference = trim($_POST['cust_reference']);
  $amount = (float)$_POST['hidden_amount'];
  $discount = (float)$_POST['discounted_price'];

  // if ($balance < $discount) {
  // 	echo json_encode(['status' => 'error', 'message' => 'Insufficient balance']);
  // 	exit;
  // }

  $validation_endpoint = SWIFT_BASE_URL . "Username=" . SWIFT_USERNAME . "&" . "Password=" . SWIFT_PASSWORD . "&" . "Partner=" . SWIFT_PARTNER . "&customer_id=" . urlencode($cust_reference);

  // Initialize cURL
  $ch = curl_init();
  curl_setopt_array($ch, [
    CURLOPT_URL => $validation_endpoint,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false
  ]);

  $response = curl_exec($ch);

  // Parse the XML response
  $xml = simplexml_load_string($response);
  
  if ($xml === false) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to parse XML response']);
    exit;
  }
  
  // Extract the required values
  $CustomerId = (string)$xml->Customer->CustomerId;
  $FirstName = (string)$xml->Customer->FirstName;
  $LastName = (string)$xml->Customer->LastName;
  $StatusCode = (string)$xml->Customer->StatusCode;
  $StatusDescription = (string)$xml->Customer->StatusDescription;
  
  if ($StatusCode === '0') {
    $log_id = date('YmdHis');
    $payment_date = date('Y-m-d H:i:s');
    echo $xml_request = <<<XML
    <?xml version="1.0" encoding="utf-8"?>
    <PaymentNotificationRequest>
        <Payments>
            <Payment>
                <PaymentLogId>{$log_id}</PaymentLogId>
                <CustReference>{$CustomerId}</CustReference>
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
  
  	$url = SWIFT_BASE_URL1 . "Username=" . SWIFT_USERNAME . "&Password=" . SWIFT_PASSWORD . "&Partner=" . SWIFT_PARTNER;

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
    if (curl_errno($ch)) {
        echo json_encode(['status' => 'error', 'message' => 'Curl error: ' . curl_error($ch)]);
        curl_close($ch);
        fclose($debug);
        exit;
    }
    curl_close($ch);
  
  	// Parse the XML response
	$xml = simplexml_load_string($response);

	if ($xml === false) {
      echo "Failed to parse XML\n";
      exit;
	}

	// Extract values
	$PaymentLogId = (string)$xml->Payments->Payment->PaymentLogId;
	$Status = (string)$xml->Payments->Payment->Status;

	// Output the results
	echo "PaymentLogId: " . $PaymentLogId . "\n";  // Output: PaymentLogId: 0
	echo "Status: " . $Status . "\n";              // Output: Status: 1
  
    if ($Status === '0') {
      // update wallet balance
      $new_balance = $balance - $discount;
      $update_sql = "UPDATE wallets SET balance = ? WHERE user_id = ?";
      $stmt = $conn->prepare($update_sql);
      $stmt->bind_param("di", $new_balance, $_SESSION['user_id']);
      if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Balance update failed']);
        exit;
      }
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Purchase failed, try again later']);
      // exit;
    }
    // log transaction and redirect to receipt
    $fullname = $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];
    $phone_number = $_SESSION['phone'];
    $product_description = 'Purchase of SWIFT Bundle';
    $status = $Status === '0' ? 'success' : 'failed';
    $type = 'SWIFT Bundle Purchase';
    $detail = 'SWIFT Bundle';
    $ref_id = date('YmdHis');
    $profit = 0;
    $insert_sql = "INSERT INTO transactions (user_id, fullname, phone_number, product_description, amount, status, type, detail, transaction_ref, profit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    if ($stmt) {
      $stmt->bind_param("isssdsssss", $_SESSION['user_id'], $fullname, $phone_number, $product_description, $discount, $status, $type, $detail, $ref_id, $profit);
      if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Transaction loggin failed']);
        exit;
      } else {
        $transaction_id = $stmt->insert_id;
      }
      $stmt->close();
      // Redirect to receipt page
      header('Location: ../?page=receipt&id=' . $transaction_id);
      exit;
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Failed to log transaction']);
      exit;
    }
    
  } else {
    echo json_encode(['status' => 'error', 'message' => $StatusDescription]);
    exit;
  }
  
  // Close the debug file at the end
  fclose($debug);
}
?>