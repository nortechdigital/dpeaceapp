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

  $successful_transactions_query = "SELECT COUNT(*) as successful_transactions FROM transactions WHERE status = 'success'";
  $successful_transactions_result = mysqli_query($conn, $successful_transactions_query);
  $successful_transactions_row = mysqli_fetch_assoc($successful_transactions_result);
  $successful_transactions = $successful_transactions_row['successful_transactions'];

  $failed_transactions_query = "SELECT COUNT(*) as failed_transactions FROM transactions WHERE status = 'failed' OR status = 'FAIL'";
  $failed_transactions_result = mysqli_query($conn, $failed_transactions_query);
  $failed_transactions_row = mysqli_fetch_assoc($failed_transactions_result);
  $failed_transactions = $failed_transactions_row['failed_transactions'];

  $pending_transactions_query = "SELECT COUNT(*) as pending_transactions FROM transactions WHERE status = 'pending'";
  $pending_transactions_result = mysqli_query($conn, $pending_transactions_query);
  $pending_transactions_row = mysqli_fetch_assoc($pending_transactions_result);
  $pending_transactions = $pending_transactions_row['pending_transactions'];

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

  include 'bal_vas2net.php';
  include 'bal_smile.php';
  include 'bal_nomiworld.php';
// print_r($bal_nomiworld);

// Function to get user details
function getUserDetails($apiKey) {
    $url = "https://arewaglobal.co/api/user/";
    

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
$apiKey = "4y9Emxo5C7x56qC9Cep4HBDvbA1CJgGrAlCBCB3A8dC3cxcCkxfB2bA3I1Ai";
$userDetails = getUserDetails($apiKey);
 "<pre>" . print_r($userDetails, true) . "</pre>";
$balance = $userDetails['balance'];

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
		<?php if($_SESSION['role']=='admin'):?>
        	<h2 class="text-center bg-primary text-light h5 mb-5 py-2">ADMIN DASHBOARD</h2>
        <?php else: ?>
        	<h2 class="text-center bg-primary text-light h5 mb-5 py-2">CUSTOMER CARE DASHBOARD</h2>
        <?php endif; ?>
        <div class="row g-3">
          
        <?php if($_SESSION['role']=='admin'):?>
          <div class="col-lg-4">
              <div class="card mb-3 shadow" style="height:130px">
                <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                <h2><span class="text-primary">&#8358;<?php echo number_format($bal_vas2net, 2); ?></span></h2>
                <?php if($bal_vas2net <= '3000000'){
                     echo "<script>alert('VAS2NETS WALLET BALANCE IS LESS THAN N3,000,000!');</script>";
         
                }
                ?>
                <hr>
                    <h5>VAS2NET</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-4">
            <div class="card mb-3 shadow" style="height:130px">
                  <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                    <h2><span class="text-primary">&#8358;<?php echo $balance; ?></span></h2>
                    <?php if($balance <= '3000000'){
                         echo "<script>alert('SMILE WALLET BALANCE IS LESS THAN N3,000,000!');</script>";
            
                    }
                    ?>
                    <hr>
                    <h5>Arewa Global (Smile)</h5>
                  </a>
              </div>
          </div>


          <div class="col-lg-4">
            <div class="card mb-3 shadow" style="height:130px">
                <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                    <h2><span class="text-primary">&#8358;<?php echo number_format($bal_nomiworld, 2); ?></span></h2>
                	<?php if($bal_nomiworld <= '3000000'){
                     	echo "<script>alert('NOMIWORLD WALLET BALANCE IS LESS THAN N3,000,000!');</script>";
         
                	}
                	?>
                    <hr>
                    <h5>NOMIWORLD </h5>
                  </a>
              </div>
          </div>

          <?php endif; ?>
          
          <div class="col-lg-4">
              <div class="card mb-3 shadow" style="height:130px">
                  <a href="./?page=successful_transactions" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                      <h2><span class="text-primary"><?php echo $successful_transactions > 0 ? $successful_transactions : 0; ?></span></h2>
                      <hr>
                      <h5>Successful Transactions</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-4">
              <div class="card mb-3 shadow" style="height:130px">
                  <a href="./?page=pending_transactions" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                      <h2><span class="text-primary"><?php echo $pending_transactions > 0 ? $pending_transactions : 0; ?></span></h2>
                      <hr>
                      <h5>Pending Transactions</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-4">
              <div class="card mb-3 shadow" style="height:130px">
                  <a href="./?page=failed_transactions" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                      <h2><span class="text-primary"><?php echo $failed_transactions > 0 ? $failed_transactions : 0; ?></span></h2>
                      <hr>
                      <h5>Failed Transactions</h5>
                  </a>
              </div>
          </div>

          <?php if($_SESSION['role']=='admin'): ?>
          <div class="col-lg-4">
              <div class="card mb-3 shadow" style="height:130px">
                  <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                      <h2><span class="text-primary"><?php echo $user_count > 0 ? $user_count : 0; ?></span></h2>
                      <hr>
                      <h5>Registered Users</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-4 d-none">
              <div class="card mb-3 shadow" style="height:130px">
                  <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                  <h2><span class="text-primary"><?php echo $subscriber_count > 0 ? $subscriber_count : 0; ?></span></h2>
                      <hr>
                      <h5>Users (Subscribers)</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-4 d-none">
              <div class="card mb-3 shadow" style="height:130px">
                  <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                  <h2><span class="text-primary"><?php echo $agent_count > 0 ? $agent_count : 0; ?></span></h2>
                      <hr>
                      <h5>Users (Agent)</h5>
                  </a>
              </div>
          </div>

          <div class="col-lg-4 d-none">
              <div class="card mb-3 shadow" style="height:130px">
                  <a href="" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                  <h2><span class="text-primary"><?php echo $vendors_count > 0 ? $vendors_count : 0; ?></span></h2>
                      <hr>
                      <h5>Users (Vendors)</h5>
                  </a>
              </div>
          </div>
        <?php endif; ?>
            
      </div>

    </div>
  </div>
</div>

<div class="row">
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
                  <td><?php echo $transaction['fullname']; ?></td>
                  <td><?php echo $transaction['phone_number']; ?></td>
                  <td><?php echo $transaction['detail']; ?></td>
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

<?php
$sql = "SELECT id, email FROM users";
$rs = $conn->query($sql);
if ($rs->num_rows > 0) {
  while($row = $rs->fetch_assoc()) {
    $sql = "SELECT created_at FROM transactions WHERE user_id = " . $row['id'] . " ORDER BY created_at DESC LIMIT 1";
    $rs2 = $conn->query($sql);
    if ($rs2->num_rows > 0) {
      $lastTransaction = $rs2->fetch_assoc();
      $lastTransactionDate = new DateTime($lastTransaction['created_at']);
      $currentDate = new DateTime();
      $interval = $currentDate->diff($lastTransactionDate);
      if ($interval->days > 21 && $interval->days <= 28) {
        $to = $row['email'];
        $subject = "Account Inactivity Notification";
        $message = "Dear User,\n\nWe noticed that your account has been inactive for over 30 days. Please log in to your account to keep it active.\n\nBest regards,\nYour Company";
        if (mail($to, $subject, $message)) {
          echo "Notification sent to " . $row['email'] . "<br>";
        } else {
          echo "Failed to send notification to " . $row['email'] . "<br>";
        }
      } elseif ($interval->days > 28) {
        $userId = $row['id'];
        // First delete all transactions for this user
        $conn->query("DELETE FROM transactions WHERE user_id = $userId");
        // Delete all wallet accounts for this user
        $conn->query("DELETE FROM wallet_accounts WHERE user_id = $userId");
        // Delete all wallets for this user
        $conn->query("DELETE FROM wallets WHERE user_id = $userId");
        // Now delete the user
        $sql = "DELETE FROM users WHERE id = $userId";
        if ($conn->query($sql) === TRUE) {
          echo "User with ID " . $userId . " has been deleted due to inactivity.<br>";
        } else {
          echo "Error deleting user: " . $conn->error . "<br>";
        }
      }
    }
  }
}
$conn->close();
?>