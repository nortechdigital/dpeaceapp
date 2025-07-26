<?php
  include "../conn.php";
  // session_start();
  // Check if the user is logged in
  if (!isset($_SESSION['user_id'])) {
      // If not logged in, redirect to the login page
      header("Location: ./?page=login");
      exit;
      
  }

  // Handle user deletion
  if (isset($_GET['delete_user_id'])) {
      $user_id = $_GET['delete_user_id'];

      // Delete user from users table
      $delete_user_query = "DELETE FROM users WHERE id = $user_id";
      mysqli_query($conn, $delete_user_query);

      // Delete user from wallet table
      $delete_wallet_query = "DELETE FROM wallet WHERE user_id = $user_id";
      mysqli_query($conn, $delete_wallet_query);

      // Delete user from transactions table
      $delete_transactions_query = "DELETE FROM transactions WHERE user_id = $user_id";
      mysqli_query($conn, $delete_transactions_query);

      // Add more delete queries for other tables as needed

      // Redirect to users page after deletion
      header("Location: users.php");
      exit;
  }

  // Query to get all users
  $all_users_query = "SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC";
  $all_users_result = mysqli_query($conn, $all_users_query);
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
        <h2 class="text-center bg-primary text-light h5  mb-2 py-2">Registered Users</h2>
        <div class="row g-3">
          <div class="row mt-5">
            <div class="col-lg-12">
              <table id="allUsers" class="table table-striped">
                <thead>
                  <tr>
                    <th>SN</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Category</th>
                    <th>Role</th>
                    <th>Date</th>
                    <th>Actions</th> <!-- Added column for actions -->
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
                      <td><?php echo $user['category']; ?></td>
                      <td><?php echo $user['role']; ?></td>
                      <td><?php echo $user['created_at']; ?></td>
                      <td>
                        <a href="index.php?page=view_user&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">View</a>
                        <a href="index.php?page=users&delete_user_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
    </div>
  </div>
</div>




<script>
  // Initialize DataTables -->
  $(document).ready(function() {
    $('#allUsers').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true
    });
  });
</script>
