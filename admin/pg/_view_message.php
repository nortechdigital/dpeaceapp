<?php
  include "../conn.php";
//   session_start();

  // Check if the user is logged in
  if (!isset($_SESSION['user_id'])) {
      header("Location: ./?page=login");
      exit;
  }

  // Get the message ID from the URL
  if (isset($_GET['id'])) {
      $message_id = $_GET['id'];
  } else {
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

  if (!$message) {
      header("Location: ./?page=messages");
      exit;
  }

  // Handle the reply submission
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply'])) {
      $user_id = $_SESSION['user_id']; // Logged-in user
      $reply = mysqli_real_escape_string($conn, $_POST['reply']);

      $insert_reply_query = "INSERT INTO replies (message_id, user_id, reply) VALUES ($message_id, $user_id, '$reply')";
      if (!mysqli_query($conn, $insert_reply_query)) {
          die("Error inserting reply: " . mysqli_error($conn));
      }
      header("Location: ?page=view_message&id=$message_id");
      exit;
  }

  // Query to get all replies for this message
  $replies_query = "SELECT replies.*, users.name FROM replies JOIN users ON replies.user_id = users.id WHERE replies.message_id = $message_id ORDER BY replies.created_at DESC";
  
  // Output the query for debugging
  echo $replies_query;

  $replies_result = mysqli_query($conn, $replies_query);

  if (!$replies_result) {
      // Output the error if the query fails
      die("Error executing query: " . mysqli_error($conn));
  }
?>

<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php"; ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
        <h2 class="text-center bg-primary text-light h5 mb-5">View Message</h2>
        <div class="row g-3">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo htmlspecialchars($message['subject']); ?></h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($message['created_at']); ?></p>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($message['name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($message['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($message['phone']); ?></p>
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

                <!-- Display existing replies -->
                <h5 class="mt-4">Replies</h5>
                <?php if (mysqli_num_rows($replies_result) > 0): ?>
                    <?php while ($reply = mysqli_fetch_assoc($replies_result)): ?>
                        <div class="card mt-3">
                            <div class="card-header">
                                <strong><?php echo htmlspecialchars($reply['name']); ?></strong> <small class="text-muted"><?php echo $reply['created_at']; ?></small>
                            </div>
                            <div class="card-body">
                                <p><?php echo nl2br(htmlspecialchars($reply['reply'])); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No replies yet.</p>
                <?php endif; ?>

                <!-- Reply Form -->
                <div class="card mt-4">
                    <div class="card-header">
                        <strong>Reply to Message</strong>
                    </div>
                    <div class="card-body">
                        <form action="?page=view_message&id=<?php echo $message_id; ?>" method="post">
                            <div class="form-group">
                                <textarea class="form-control" name="reply" rows="4" placeholder="Write your reply here..." required></textarea>
                            </div>
                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary">Send Reply</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>
