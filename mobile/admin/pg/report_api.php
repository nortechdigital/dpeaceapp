<?php
  include "../conn.php";
  //session_start();
  // Check if the user is logged in
  if (!isset($_SESSION['user_id'])) {
      // If not logged in, redirect to the login page
      header("Location: ./?page=login");
      exit;
  }
 // Query to get all API configurations
 $api_query = "SELECT * FROM api_management ORDER BY provider";
 $api_result = mysqli_query($conn, $api_query);
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<div class="container py-2">
  <h2 class="text-center bg-primary text-light h5 mb-2 py-2">API's Report</h2>
  <div class="row">
    <div class="col-lg-12">
    <button onclick="history.back()" class="btn btn-primary mb-3">Back</button>
        <table id="apiTable" class="table table-striped">
            <thead>
              <tr>
                <th>SN</th>
                <th>Provider</th>
                <th>API Key</th>
                <th>Endpoint</th>
              </tr>
            </thead>
            <tbody>
            <?php 
            $sn = 0; 
            while($api = mysqli_fetch_assoc($api_result)): 
                $sn += 1;
            ?>
                <tr>
                    <td><?php echo $api['id']; ?></td>
                    <td><?php echo $api['provider']; ?></td>
                    <td><?php echo $api['api_key']; ?></td>
                    <td><?php echo $api['endpoint']; ?></td>
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
    $('#apiTable').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true
    });
  });
</script>