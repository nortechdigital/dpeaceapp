<?php
  include "../conn.php";
  // session_start();
  // Check if the user is logged in
  if (!isset($_SESSION['user_id'])) {
      // If not logged in, redirect to the login page
      header("Location: ./?page=login");
      exit;
  }

  // Query to get all messages (fixed query)
  $all_messages_query = "SELECT * FROM messages ORDER BY created_at DESC";
  $all_messages_result = mysqli_query($conn, $all_messages_query);

  if (!$all_messages_result) {
      die("Error executing query: " . mysqli_error($conn));
  }
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
        <h2 class="text-center bg-primary text-light h5 mb-5 py-2">System Messages</h2>
        <div class="row g-3">
            <div class="row mt-3">
                <div class="col-lg-12">
                <table id="messages" class="table table-striped">
                    <thead>
                    <tr>
                        <th>SN</th>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th class="text-right">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $sn = 1; while($messages = mysqli_fetch_assoc($all_messages_result)): ?>
                        <tr>
                        <td><?php echo $sn; ?></td>
                        <td><?php echo $messages['created_at']; ?></td>
                        <td><?php echo $messages['name']; ?></td>
                        <td><?php echo $messages['email']; ?></td> 
                        <td><?php echo $messages['subject']; ?></td>
                        <td class="text-right">
                            <div class="actions">
                                <a class="btn btn-sm btn-warning" href="?page=view_message&id=<?php echo $messages['id']; ?>">
                                    <i class="fe fe-eye"></i> View
                                </a>
                                <form class="d-inline-block" action="_/delete.php" method="post" onsubmit="return confirm('Are you sure want to delete?')">
                                    <input type="hidden" name="tbl" value="messages">
                                    <input type="hidden" name="id" value="<?php echo $messages['id'] ?>">
                                    <button type="submit" class="btn btn-sm bg-danger">
                                        <i class="fe fe-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                        </tr>
                    <?php $sn++; endwhile; ?>
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
    $('#messages').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true
    });
  });
</script>
