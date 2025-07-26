<?php
  include "../conn.php";
  //session_start();
  // Check if the user is logged in
  if (!isset($_SESSION['user_id'])) {
      // If not logged in, redirect to the login page
      header("Location: ./?page=login");
      exit;
  }

  // Query to get all transactions
  $transactions_query = "SELECT * FROM transactions ORDER BY created_at DESC";
  $transactions_result = mysqli_query($conn, $transactions_query);
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<div class="container py-2">
  <h2 class="text-center bg-primary text-light h5 mb-5 py-2">Transactions Report</h2>
  <div class="row mt-5">
    <button onclick="history.back()" class="btn btn-primary mb-3">Back</button>
    <div class="col-lg-12">
      <table id="transactions" class="table table-striped">
        <thead>
          <tr>
            <th>SN</th>
            <th>Customer Name</th>
            <th>Phone</th>
            <th>Type</th>
            <th>Product Description</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php 
            $sn = 0; 
            while($transaction = mysqli_fetch_assoc($transactions_result)): 
                $sn += 1;
          ?>
            <tr>
              <td><?php echo $sn; ?></td>
              <td><?php echo $transaction['username']; ?></td>
              <td><?php echo $transaction['phone_number']; ?></td>
              <td><?php echo $transaction['type']; ?></td>
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

<!-- Initialize DataTables -->
<script>
  $(document).ready(function() {
    $('#transactions').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true
    });
  });
</script>