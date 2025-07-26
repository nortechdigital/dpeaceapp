<?php
  session_start();
  include "../conn.php";
  include "../_/ac_config.php";

  if (!isset($_SESSION['user_id'])) {
      echo json_encode(['status' => 'error', 'message' => 'User session not found']);
      exit;
  }

  // Function to buy data
  function buySmileData($apiKey, $phoneNumber, $BundleTypeCode, $actype) {
      $url = "https://arewaglobal.co/api/smile-data/";

      $data = [
          "PhoneNumber" => $phoneNumber,
          "BundleTypeCode" => $BundleTypeCode,
          "actype" => $actype
      ];
      $ch = curl_init();

      // Set cURL options
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, [
          "Authorization: Token " . $apiKey,
          "Content-Type: application/json"
      ]);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

      // Execute the cURL request
      $response = curl_exec($ch);

      // Get HTTP code before closing cURL
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

      // Check for errors
      if ($response === false) {
        echo "<script>alert('Error: " . curl_error($ch) . "');window.location.href='../?page=data';</script>";
          exit;
      }

      // Close the cURL session
      curl_close($ch);

      // Decode and return the response
      return ['httpCode' => $httpCode, 'response' => json_decode($response, true)];
  }

// Function to validate Smile phone numbers
  function is_smile_number($phone_number) {
      // Define Smile network prefixes
      $smile_prefixes = ['2347020']; // Add actual Smile prefixes here

      // Normalize the phone number (remove spaces, dashes, etc.)
      $normalized_number = preg_replace('/\D/', '', $phone_number);

      // Check if the phone number starts with any Smile prefix
      foreach ($smile_prefixes as $prefix) {
          if (strpos($normalized_number, $prefix) === 0) {
              return true;
          }
      }

      return false;
  }

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $actype = $_POST['actype'];
      $BundleTypeCode = $_POST['bundleTypeCode'];
      $amount = (float)$_POST['hidden_amount'];
      $discount = (float)$_POST['discounted_price'];
      $phoneNumber = $_POST['phone'];
      $requestId = date('YmdHis');
      $fullname = $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];
      $email = $_SESSION['email'] ?? '';

      $sql = "SELECT * FROM wallets WHERE user_id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $_SESSION['user_id']);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows === 0) {
          echo "<script>alert('User not found!');window.location.href='../?page=data';</script>";
          exit;
      }

      $row = $result->fetch_assoc();
      $balance = $row['balance'];

   // Validate if the phone number belongs to Smile network
      if (!is_smile_number($phoneNumber)) {
      	 $_SESSION['error'] = "Invalid phone number for selected provider. SMILE number expected.";
         die(header('Location: ' . $_SERVER['HTTP_REFERER']));
      }
  
      if ($balance < $discount) {
          echo "<script>alert('Insufficient balance. Please fund your Wallet!');window.location.href='../?page=data';</script>";
          exit;
      } else {
          $apiKey = "4y9Emxo5C7x56qC9Cep4HBDvbA1CJgGrAlCBCB3A8dC3cxcCkxfB2bA3I1Ai";

          $apiResponse = buySmileData($apiKey, $phoneNumber, $BundleTypeCode, $actype);
         echo $httpCode = $apiResponse['httpCode'];
         echo $responseData = $apiResponse['response'];


          }

          // Process response
          $status = strtolower($responseData['data']['status'] ?? 'success');
          $transref = date('YmdHis');
          $ref_id = date('YmdHis');
          $product_description = "Purchase of Smile Bundle";
          $type = 'Smile Bundle Purchase';
          $detail = "Smile Bundle";
  		  $cust_discount = $amount - $discount;
  		  $current_balance = $balance;
  		   

          // Update wallet if successful
          if ($status === 'success') {
              $new_balance = $balance - $discount;
              $update_sql = "UPDATE wallets SET balance = ? WHERE user_id = ?";
              $stmt = $conn->prepare($update_sql);
              $stmt->bind_param("di", $new_balance, $_SESSION['user_id']);
              if (!$stmt->execute()) {
                  error_log("Balance update failed: " . $stmt->error);
              }
          }

          // Log transaction
          $profit = 0;
  		  $new_balance = $balance - $discount;
          $insert_sql = "INSERT INTO transactions (user_id, fullname, phone_number, product_description, amount, status, type, detail, transaction_ref, profit, cust_discount, current_balance, new_balance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
          $stmt = $conn->prepare($insert_sql);
          if ($stmt) {
              $stmt->bind_param("isssdssssdddd", $_SESSION['user_id'], $fullname, $phoneNumber, $product_description, $discount, $status, $type, $detail, $ref_id, $profit, $cust_discount, $current_balance, $new_balance);
              if (!$stmt->execute()) {
                  error_log("Transaction logging failed: " . $stmt->error);
              }
              $transaction_id = $stmt->insert_id;
              $stmt->close();
          } else {
              error_log("Prepare failed: " . $conn->error);
          }

          // Set session data for receipt
          $_SESSION['re'] = [
              'status' => $status,
              'request_id' => $transref,
              'fullname' => $fullname,
              'phone_number' => $phoneNumber,
              'product_description' => $product_description,
              'amount' => $discount,
              'created_at' => date('Y-m-d H:i:s'),
              'ref_id' => $ref_id
          ];

          // Send email receipt if successful
          if ($status === 'success' && !empty($email)) {
              $to = $email;
              $subject = 'DPeace App Receipt - Smile Bundle Purchase';
              $message = '
              <html>
              <head>
              	<div class="header">
                    <img src="https://dpeaceapp.com/img/dpeace-app.png" alt="DPeace Logo" class="logo">
                    <h1>Smile Bundle Purchase - Receipt</h1>
                </div>
                  <style>
                     body { font-family: Arial, sans-serif; }
                     .header { color: #3366ff; text-align: center; }
                     .content { margin: 20px 0; }
                     .footer { font-size: 12px; color: #666; text-align: center; }
                     .logo { display: block; margin: 0 auto; width: 150px; }
                 </style>
            </head>
            <body>
                <div class="header">
                    <img src="https://dpeaceapp.com/img/dpeace-app.png" alt="DPeace Logo" class="logo">
                    <h1>Smile Bundle Purchase - Receipt</h1>
                </div>
                  <div class="content">
                      <p>Reference ID: '.$transref.'</p>
                      <p>Transaction Details:</p>
                      <ul>
                          <li>Name: '.$fullname.'</li>
                          <li>Description: '.$product_description.'</li>
                          <li>Phone Number: '.$phoneNumber.'</li>
                          <li>Amount: ₦'.number_format($discount, 2).'</li>
                          <li>Status: '.ucfirst($status).'</li>
                      </ul>
                  </div>
                  <div class="footer">
                      <p>© '.date('Y').' DPeaceApp. All rights reserved.</p>
                  </div>
              </body>
              </html>';

              $headers = implode("\r\n", [
                  'From: no-reply@dpeaceapp.com',
                  'Reply-To: no-reply@dpeaceapp.com',
                  'MIME-Version: 1.0',
                  'Content-type: text/html; charset=UTF-8',
                  'X-Mailer: PHP/' . phpversion()
              ]);

              mail($to, $subject, $message, $headers);
          }

          // Redirect to receipt page
          header('Location: ../?page=receipt&id=' . $transaction_id);
          exit;
      }else{
        
            $status = strtolower($responseData['data']['status'] ?? 'failed');
            $transref = date('YmdHis');
            $ref_id = date('YmdHis');
            $product_description = "Purchase of Smile Bundle";
            $type = 'Smile Bundle Purchase';
            $detail = "Smile Bundle";

            // Log transaction
            $profit = 0;
  		    $new_balance = $balance;
            $insert_sql = "INSERT INTO transactions (user_id, fullname, phone_number, product_description, amount, status, type, detail, transaction_ref, profit, current_balance, new_balance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            if ($stmt) {
                $stmt->bind_param("isssdssssddd", $_SESSION['user_id'], $fullname, $phoneNumber, $product_description, $discount, $status, $type, $detail, $ref_id, $profit, $current_balance. $new_balance);
                if (!$stmt->execute()) {
                    error_log("Transaction logging failed: " . $stmt->error);
                }
                $stmt->close();
            } else {
                error_log("Prepare failed: " . $conn->error);
            }

              $errorMsg = $responseData['msg'] ?? 'Unknown error occurred!!?';
              echo "<script>alert('Transaction failed: $errorMsg');window.location.href='../?page=data';</script>";
              exit;
      }
?>