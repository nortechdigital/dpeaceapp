<?php
  include "../conn.php";
//   session_start();

  // Check if the user is logged in
  if (!isset($_SESSION['user_id'])) {
      // If not logged in, redirect to the login page
      header("Location: ./?page=login");
      exit;
  }

  // Get the message ID from the URL
  if (isset($_GET['id'])) {
      $message_id = $_GET['id'];
  } else {
      // If no ID is provided, redirect to the message list page
      header("Location: ./?page=messages");
      exit;
  }

  // Query to get the specific message details
  $message_query = "SELECT * FROM messages WHERE id = $message_id";
  $message_result = mysqli_query($conn, $message_query);

  if (!$message_result) {
      die("Error executing query: " . mysqli_error($conn));
  }

  // Fetch the message data
  $message = mysqli_fetch_assoc($message_result);

  // If no message is found, redirect to the message list page
  if (!$message) {
      header("Location: ./?page=messages");
      exit;
  }
?>

<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php"; ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
        <h2 class="text-center bg-primary text-light h5 mb-5 py-2">View Message</h2>
        <div class="row g-3">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <p class="card-title">Sender: <?php echo htmlspecialchars($message['name']); ?></p>
                        <hr>
                        <p class="card-title">Subject: <?php echo htmlspecialchars($message['subject']); ?></p>
                        <hr>
                        <p class="card-title">Email: <?php echo htmlspecialchars($message['email']); ?></p>
                        <hr>
                        <p class="card-title">Phone: <?php echo htmlspecialchars($message['phone']); ?></p>
                    </div>
                    <div class="card-body">
                        <p><strong>Message:</strong></p>
                        <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                    </div>
                    <div class="card-footer text-right">
                        <a class="btn btn-primary" href="?page=messages">Back to Messages</a>
                        <form class="d-inline-block" action="_/delete.php" method="post" onsubmit="return confirm('Are you sure you want to delete this message?')">
                            <input type="hidden" name="tbl" value="messages">
                            <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                            <button type="submit" class="btn btn-danger">
                                <i class="fe fe-trash"></i> Delete Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>
