<?php
  include "../conn.php";
  //session_start();
  // Check if the user is logged in
  if (!isset($_SESSION['user_id'])) {
      // If not logged in, redirect to the login page
      header("Location: ./?page=login");
      exit;
  }

 // Query to get all users
 $all_users_query = "SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC";
 $all_users_result = mysqli_query($conn, $all_users_query);
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<div class="container py-2">
  <h2 class="text-center bg-primary text-light h5 mb-2 py-2">Users Report</h2>
  <div class="row">
    <div class="col-lg-12">
      <button onclick="history.back()" class="btn btn-primary mb-3">Back</button>
      <table id="allUsers" class="table table-striped">
                <thead>
                  <tr>
                    <th>SN</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Category</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $sn = 0; 
                    while($user = mysqli_fetch_assoc($all_users_result)): 
                        $sn += 1;
                  ?>
                    <tr>
                      <td><?php echo $sn; ?></td>
                      <td><?php echo $user['firstname']; ?></td>
                      <td><?php echo $user['lastname']; ?></td>
                      <td><?php echo $user['email']; ?></td>
                      <td><?php echo $user['phone']; ?></td>
                      <td><?php echo $user['role']; ?></td>
                      <td><?php echo $user['category']; ?></td>
                      <td><?php echo $user['created_at']; ?></td>
                      
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
    $('#allUsers').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true
    });
  });
</script>