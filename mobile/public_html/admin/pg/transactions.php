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

<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">
  <div class="container py-2">
  <h2 class="text-center bg-primary text-light h5 mb-2 py-2">All Transactions</h2>
  <div class="row mt-5">
    <div class="col-lg-12">
      <table id="transactions" class="table table-striped">
        <thead>
          <tr>
            <th>SN</th>
            <!-- <th>Request ID</th> -->
            <th>Customer Name</th>
            <!-- <th>Phone</th> -->
            <th>Product Description</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
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
              <!-- <td><?php echo $transaction['request_id']; ?></td> -->
              <td><?php echo $transaction['fullname']; ?></td>
              <!-- <td><?php echo $transaction['phone_number']; ?></td> -->
              <td><?php echo $transaction['product_description']; ?></td>
              <td>&#8358;<?php echo number_format($transaction['amount'], 2); ?></td>
              <td><?php echo $transaction['status']; ?></td>
              <td><?php echo $transaction['created_at']; ?></td>
              <td>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#transactionModal<?php echo $transaction['id']; ?>">Details</button>

              <!-- Modal -->
              <div class="modal fade" id="transactionModal<?php echo $transaction['id']; ?>" tabindex="-1" aria-labelledby="transactionModalLabel<?php echo $transaction['id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="transactionModalLabel<?php echo $transaction['id']; ?>">Transaction Details</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered">
                        <tr>
                          <th>Transaction Date</th>
                          <td><?php echo $transaction['created_at']; ?></td>
                        </tr>
                        <tr>
                          <th>Request ID</th>
                          <td><?php echo $transaction['transaction_ref']; ?></td>
                        </tr>
                        <tr>
                          <th>Customer Name</th>
                          <td><?php echo $transaction['fullname']; ?></td>
                        </tr>
                        <tr>
                          <th>Phone</th>
                          <td><?php echo $transaction['phone_number']; ?></td>
                        </tr>
                        <tr>
                          <th>Product Description</th>
                          <td><?php echo $transaction['product_description']; ?></td>
                        </tr>
                        <tr>
                          <th>Amount</th>
                          <td>&#8358;<?php echo number_format($transaction['amount'], 2); ?></td>
                        </tr>
                        <tr>
                          <th>Status</th>
                          <td><?php echo $transaction['status']; ?></td>
                        </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    </div>
                  </div>
                </div>
              </div>
              </td>
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