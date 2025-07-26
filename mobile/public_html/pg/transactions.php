<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

// Include the database connection file
include_once './_/conn.php'; // Adjust the path as necessary

// Check if the database connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user transactions from the database
$user_id = $_SESSION['user_id'];
$transactions = [];
$sql = "SELECT * FROM transactions WHERE user_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);

// Check if the SQL query is prepared successfully
if ($stmt === false) {
    die("Error preparing the SQL statement: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $transactions[] = $row;
}
$stmt->close();
?>

<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
      <h2 class="text-center bg-primary text-light h5">TRANSACTION HISTORY</h2>
      <div class="row py-3">
        <div class="col-md-12">
          <div class="card shadow p-4">
            <table id="transactionTable" class="display" style="width:100%">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Description</th>
                  <th>Amount</th>
                  <th>Status</th>
				  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($transactions) > 0): ?>
                  <?php foreach ($transactions as $transaction): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($transaction['created_at']); ?></td>
                      <td><?php echo htmlspecialchars($transaction['product_description']); ?></td>
                      <td>&#8358;<?php echo htmlspecialchars(number_format($transaction['amount'], 2)); ?></td>
                      <td><?php echo htmlspecialchars($transaction['status']); ?></td>
                  	  <td>
                        <a href="?page=receipt&id=<?php echo urlencode($transaction['id']); ?>" class="btn btn-sm btn-primary">View</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="4" class="text-center">No transactions found</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Include jQuery and DataTables JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Initialize DataTable -->
<script>
  $(document).ready(function() {
    $('#transactionTable').DataTable();
  });
</script>