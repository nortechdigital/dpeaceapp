<?php
  include "../conn.php";
  include "../_/ac_config.php";
  // session_start();
  // Check if the user is logged in
  if (!isset($_SESSION['user_id'])) {
      // If not logged in, redirect to the login page
      header("Location: ./?page=login");
      exit;
  }
  
  
// Function to get user details
function getUserDetails($apiKey) {
  $url = AREWA_BASE_URL . '/api/user/';
  // $apiKey = AREWA_API_KEY;
  

  // Initialize cURL
  $ch = curl_init();

  // Set cURL options
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Authorization: Token " . $apiKey
  ]);
  curl_setopt($ch, CURLOPT_POST, true);

  // Execute the cURL request
  $response = curl_exec($ch);

  // Check for errors
  if ($response === false) {
      echo "Error: " . curl_error($ch);
  }

  // Close the cURL session
  curl_close($ch);

  // Decode and return the response
  return json_decode($response, true);
}

// Example usage
$apiKey = AREWA_API_KEY;
$userDetails = getUserDetails($apiKey);
 "<pre>" . print_r($userDetails, true) . "</pre>";
 $user_balance = $userDetails['balance'];
 
 

  // Queries to get other required data
  $query = "SELECT COUNT(*) as user_count FROM users WHERE role != 'admin'";
  $result = mysqli_query($conn, $query);
  $row = mysqli_fetch_assoc($result);
  $user_count = $row['user_count'];

  $wallet_balance_query = "SELECT SUM(balance) as wallet_balance FROM wallets";
  $wallet_balance_result = mysqli_query($conn, $wallet_balance_query);
  $wallet_balance_row = mysqli_fetch_assoc($wallet_balance_result);
  $wallet_balance = $wallet_balance_row['wallet_balance'];

  $api_balance_smile_query = "SELECT balance FROM api_balances WHERE provider = 'smile'";
  $api_balance_smile_result = mysqli_query($conn, $api_balance_smile_query);
  $api_balance_smile_row = mysqli_fetch_assoc($api_balance_smile_result);
  $api_balance_smile = $api_balance_smile_row['balance'];

  $api_balance_glo_query = "SELECT balance FROM api_balances WHERE provider = 'glo'";
  $api_balance_glo_result = mysqli_query($conn, $api_balance_glo_query);
  $api_balance_glo_row = mysqli_fetch_assoc($api_balance_glo_result);
  $api_balance_glo = $api_balance_glo_row['balance'];

  $api_balance_9mobile_query = "SELECT balance FROM api_balances WHERE provider = '9mobile'";
  $api_balance_9mobile_result = mysqli_query($conn, $api_balance_9mobile_query);
  $api_balance_9mobile_row = mysqli_fetch_assoc($api_balance_9mobile_result);
  $api_balance_9mobile = $api_balance_9mobile_row['balance'];

  $api_balance_airtel_query = "SELECT balance FROM api_balances WHERE provider = 'airtel'";
  $api_balance_airtel_result = mysqli_query($conn, $api_balance_airtel_query);
  $api_balance_airtel_row = mysqli_fetch_assoc($api_balance_airtel_result);
  $api_balance_airtel = $api_balance_airtel_row['balance'];

  $api_balance_mtn_query = "SELECT balance FROM api_balances WHERE provider = 'mtn'";
  $api_balance_mtn_result = mysqli_query($conn, $api_balance_mtn_query);
  $api_balance_mtn_row = mysqli_fetch_assoc($api_balance_mtn_result);
  $api_balance_mtn = $api_balance_mtn_row['balance'];

  $successful_transactions_query = "SELECT COUNT(*) as successful_transactions FROM transactions WHERE status = 'successful'";
  $successful_transactions_result = mysqli_query($conn, $successful_transactions_query);
  $successful_transactions_row = mysqli_fetch_assoc($successful_transactions_result);
  $successful_transactions = $successful_transactions_row['successful_transactions'];

  $failed_transactions_query = "SELECT COUNT(*) as failed_transactions FROM transactions WHERE status = 'failed'";
  $failed_transactions_result = mysqli_query($conn, $failed_transactions_query);
  $failed_transactions_row = mysqli_fetch_assoc($failed_transactions_result);
  $failed_transactions = $failed_transactions_row['failed_transactions'];

  // Query to get recent transactions
  $recent_transactions_query = "SELECT * FROM transactions ORDER BY created_at DESC";
  $recent_transactions_result = mysqli_query($conn, $recent_transactions_query);

  // Queries to get user counts by category
  $subscriber_count_query = "SELECT COUNT(*) as subscriber_count FROM users WHERE category = 'subscriber' AND role != 'admin'";
  $subscriber_count_result = mysqli_query($conn, $subscriber_count_query);
  $subscriber_count_row = mysqli_fetch_assoc($subscriber_count_result);
  $subscriber_count = $subscriber_count_row['subscriber_count'];

  $agent_count_query = "SELECT COUNT(*) as agent_count FROM users WHERE category = 'agent' AND role != 'admin'";
  $agent_count_result = mysqli_query($conn, $agent_count_query);
  $agent_count_row = mysqli_fetch_assoc($agent_count_result);
  $agent_count = $agent_count_row['agent_count'];

  $vendors_count_query = "SELECT COUNT(*) as vendors_count FROM users WHERE category = 'vendor' AND role != 'admin'";
  $vendors_count_result = mysqli_query($conn, $vendors_count_query);
  $vendors_count_row = mysqli_fetch_assoc($vendors_count_result);
  $vendors_count = $vendors_count_row['vendors_count'];
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
        <h2 class="text-center bg-primary text-light h5 mb-5 py-2">ADMIN DASHBOARD</h2>
        <div class="row g-3">
          
          <div class="col-lg-3">
              <div class="card mb-3 shadow" style="height:130px">
                <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                    <h2><span class="text-primary">&#8358;<?php echo $user_balance > 0 ? $user_balance : 0, 2 ?></span></h2>

                    <hr>
                    <h5>Arewa Global</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-3">
            <div class="card mb-3 shadow" style="height:130px">
                  <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                    <h2><span class="text-primary">&#8358;<?php echo number_format($api_balance_smile > 0 ? $api_balance_smile : 0, 2); ?></span></h2>
                    <hr>
                    <h5>Smile</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-3">
            <div class="card mb-3 shadow" style="height:130px">
                  <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                    <h2><span class="text-primary">&#8358;<?php echo number_format($api_balance_mtn > 0 ? $api_balance_mtn : 0, 2); ?></span></h2>
                    <hr>
                    <h5>MTN</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-3">
            <div class="card mb-3 shadow" style="height:130px">
                <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                    <h2><span class="text-primary">&#8358;<?php echo number_format($api_balance_airtel > 0 ? $api_balance_airtel : 0, 2); ?></span></h2>
                    <hr>
                    <h5>Airtel</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-3">
            <div class="card mb-3 shadow" style="height:130px">
                  <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                    <h2><span class="text-primary">&#8358;<?php echo number_format($api_balance_9mobile > 0 ? $api_balance_9mobile : 0, 2); ?></span></h2>
                    <hr>
                    <h5>9mobile</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-3">
              <div class="card mb-3 shadow" style="height:130px">
                  <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                  <h2><span class="text-primary">&#8358;<?php echo number_format($api_balance_glo > 0 ? $api_balance_glo : 0, 2); ?></span></h2>
                    <hr>
                    <h5>Glo</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-3">
              <div class="card mb-3 shadow" style="height:130px">
                  <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                  <h2><span class="text-primary">&#8358;<?php echo number_format($api_balance_glo > 0 ? $api_balance_glo : 0, 2); ?></span></h2>
                    <hr>
                    <h5>SWIFT</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-3">
              <div class="card mb-3 shadow" style="height:130px">
                  <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                  <h2><span class="text-primary">&#8358;<?php echo number_format($api_balance_glo > 0 ? $api_balance_glo : 0, 2); ?></span></h2>
                    <hr>
                    <h5>Spectranet</h5>
                  </a>
              </div>
          </div>
          
          <div class="col-lg-3">
              <div class="card mb-3 shadow" style="height:130px">
                  <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                      <h2><span class="text-primary"><?php echo $successful_transactions > 0 ? $successful_transactions : 0; ?></span></h2>
                      <hr>
                      <h5>Successful Transactions</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-3">
              <div class="card mb-3 shadow" style="height:130px">
                  <a href="./?page=failed_transactions" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                      <h2><span class="text-primary"><?php echo $failed_transactions > 0 ? $failed_transactions : 0; ?></span></h2>
                      <hr>
                      <h5>Failed Transactions</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-3">
              <div class="card mb-3 shadow" style="height:130px">
                  <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                      <h2><span class="text-primary"><?php echo $user_count > 0 ? $user_count : 0; ?></span></h2>
                      <hr>
                      <h5>Registered Users</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-3">
              <div class="card mb-3 shadow" style="height:130px">
                  <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                  <h2><span class="text-primary"><?php echo $subscriber_count > 0 ? $subscriber_count : 0; ?></span></h2>
                      <hr>
                      <h5>Users (Subscribers)</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-3">
              <div class="card mb-3 shadow" style="height:130px">
                  <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                  <h2><span class="text-primary"><?php echo $agent_count > 0 ? $agent_count : 0; ?></span></h2>
                      <hr>
                      <h5>Users (Agent)</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-3">
              <div class="card mb-3 shadow" style="height:130px">
                  <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                  <h2><span class="text-primary"><?php echo $vendors_count > 0 ? $vendors_count : 0; ?></span></h2>
                      <hr>
                      <h5>Users (Vendors)</h5>
                  </a>
              </div>
          </div>
            
      </div>

      <hr>
      <div class="row mt-5">
        <div class="col-lg-12">
          <h3 class="text-center">Recent Transactions</h3>
          <table id="recentTransactions" class="table table-striped">
            <thead>
              <tr>
                <th>SN</th>
                <th>Customer Name</th>
                <th>Phone</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date & Time</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $sn = 0;
                while($transaction = mysqli_fetch_assoc($recent_transactions_result)): 
                $sn += 1;
                ?>
                <tr>
                  <td><?php echo $sn; ?></td>
                  <td><?php echo $transaction['username']; ?></td>
                  <td><?php echo $transaction['phone_number']; ?></td>
                  <td><?php echo $transaction['product_description']; ?></td>
                  <td>&#8358;<?php echo number_format($transaction['amount'], 2); ?></td>
                  <td><?php echo $transaction['status']; ?></td>
                  <td><?php echo $transaction['created_at']; ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Initialize DataTables -->
<script>
  $(document).ready(function() {
    $('#recentTransactions').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true
    });
    $('#userCategories').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true
    });
  });
</script>
