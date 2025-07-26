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
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<div class="container py-2">
  <h2 class="text-center bg-primary text-light h5 mb-2 py-2">Transactions Report</h2>
  <div class="row">
    <div class="col-lg-12">
    <button onclick="history.back()" class="btn btn-primary mb-3">Back</button>
      <table id="transactions" class="table table-striped">
        <thead>
          <tr>
            <th>SN</th>
            <th>Customer Name</th>
            <th>Phone</th>
            <!-- <th>Type</th> -->
            <th>Product Description</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Transaction Type</th>
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
              <td><?php echo $transaction['fullname']; ?></td>
              <td><?php echo $transaction['phone_number']; ?></td>
              <!-- <td><?php echo $transaction['type']; ?></td> -->
              <td><?php echo $transaction['product_description']; ?></td>
              <td>&#8358;<?php echo number_format($transaction['amount'], 2); ?></td>
              <td><?php echo $transaction['status']; ?></td>
              <td><?php echo $transaction['type']; ?></td>
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
      "searching": false,
      "ordering": true,
      "info": true,
      dom: 'Bfrtip',
      buttons: [
        'copy', 'csv', 'excel', 'pdf', 'print'
      ]
    });
  });
</script>