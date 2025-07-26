<?php
  include "../conn.php";
  //session_start();
  // Check if the user is logged in
  if (!isset($_SESSION['user_id'])) {
      // If not logged in, redirect to the login page
      header("Location: ./?page=login");
      exit;
  }

  // Query to get all messages (fixed query)
  $all_messages_query = "SELECT * FROM messages ORDER BY created_at DESC";
  $all_messages_result = mysqli_query($conn, $all_messages_query);

?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<div class="container py-2">
  <h2 class="text-center bg-primary text-light h5 mb-2 py-2">Messages Report</h2>
  <div class="row">
    <div class="col-lg-12">
    <button onclick="history.back()" class="btn btn-primary mb-3">Back</button>
      <table id="messages" class="table table-striped">
        <thead>
          <tr>
            <th>SN</th>
            <th>Date</th>
            <th>Subject</th>
            <th>Sender</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Message</th>
          </tr>
        </thead>
        <tbody>
        <?php $sn = 0; while($messages = mysqli_fetch_assoc($all_messages_result)):$sn += 1; ?>
            <tr>
                <td><?php echo $sn; ?></td>
                <td><?php echo $messages['created_at']; ?></td>
                <td><?php echo $messages['subject']; ?></td>
                <td><?php echo $messages['name']; ?></td>
                <td><?php echo $messages['email']; ?></td> 
                <td><?php echo $messages['phone']; ?></td>
                <td><?php echo $messages['message']; ?></td>
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
    $('#messages').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true
    });
  });
</script>