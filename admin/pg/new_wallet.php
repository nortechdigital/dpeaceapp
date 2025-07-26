<?php
    include "../conn.php";
    // session_start();
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        // If not logged in, redirect to the login page
        header("Location: ./?page=login");
        exit;
    }

    // Handle form submissions for adding, updating, deleting, transferring, and deducting wallet entries
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['add_wallet'])) {
            $user_id = $_POST['user_id'];
            $balance = $_POST['balance'];
            $insert_query = "INSERT INTO wallets (user_id, balance) VALUES ('$user_id', '$balance')";
            mysqli_query($conn, $insert_query);
        } elseif (isset($_POST['update_wallet'])) {
            $id = $_POST['id'];
            $user_id = $_POST['user_id'];
            $balance = $_POST['balance'];
            $update_query = "UPDATE wallets SET user_id='$user_id', balance='$balance' WHERE id='$id'";
            mysqli_query($conn, $update_query);
        } elseif (isset($_POST['delete_wallet'])) {
            $id = $_POST['id'];
            $delete_query = "DELETE FROM wallets WHERE id='$id'";
            mysqli_query($conn, $delete_query);
        } elseif (isset($_POST['transfer_balance'])) {
            $id = $_POST['id'];
            $amount = $_POST['amount'];
            $wallet_query = "SELECT balance FROM wallets WHERE id='$id'";
            $wallet_result = mysqli_query($conn, $wallet_query);
            $wallet = mysqli_fetch_assoc($wallet_result);
            if ($wallet['balance'] >= $amount) {
                $new_balance = $wallet['balance'] - $amount;
                $update_wallet_query = "UPDATE wallets SET balance='$new_balance' WHERE id='$id'";
                mysqli_query($conn, $update_wallet_query);
                $user_id = $_POST['user_id'];
                $update_user_balance_query = "UPDATE users SET balance = balance + '$amount' WHERE id='$user_id'";
                mysqli_query($conn, $update_user_balance_query);
            }
        } elseif (isset($_POST['deduct_balance'])) {
            $id = $_POST['id'];
            $amount = $_POST['amount'];
            $user_query = "SELECT balance FROM users WHERE id='$id'";
            $user_result = mysqli_query($conn, $user_query);
            $user = mysqli_fetch_assoc($user_result);
            if ($user['balance'] >= $amount) {
                $new_balance = $user['balance'] - $amount;
                $update_user_balance_query = "UPDATE users SET balance='$new_balance' WHERE id='$id'";
                mysqli_query($conn, $update_user_balance_query);
                $wallet_id = $_POST['wallet_id'];
                $update_wallet_query = "UPDATE wallets SET balance = balance + '$amount' WHERE id='$wallet_id'";
                mysqli_query($conn, $update_wallet_query);
            }
        }
    }

    // Query to get all wallet entries
    $wallet_query = "SELECT w.id, u.firstname, u.lastname, w.balance FROM wallets w JOIN users u ON w.user_id = u.id ORDER BY u.firstname ASC";
    $wallet_result = mysqli_query($conn, $wallet_query);
?>


<div class="row">
    <div class="col-lg-2">
        <?php include "./inc/sidebar.php" ?>
    </div>
    <div class="col-lg-10">
        <div class="container py-2">
            <h2 class="text-center bg-primary text-light h5 mb-5">Wallet Management</h2>
            <div class="row g-3">
                <div class="row mt-3">
                    <div class="col-lg-12">
                        <button type="button" class="btn btn-primary float-right mb-3" data-toggle="modal" data-target="#AddWalletModal">Add Wallet</button>
                        
                        <table id="wallets" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User Name</th>
                                    <th>Balance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($wallet = mysqli_fetch_assoc($wallet_result)): ?>
                                    <tr>
                                        <td><?php echo $wallet['id']; ?></td>
                                        <td><?php echo $wallet['firstname'] . ' ' . $wallet['lastname']; ?></td>
                                        <td>&#8358;<?php echo number_format($wallet['balance'], 2); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editWallet(<?php echo $wallet['id']; ?>, '<?php echo $wallet['user_id']; ?>', '<?php echo $wallet['balance']; ?>')">Edit</button>
                                            <form method="POST" action="" class="d-inline-block">
                                                <input type="hidden" name="id" value="<?php echo $wallet['id']; ?>">
                                                <button type="submit" name="delete_wallet" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this wallet?')">Delete</button>
                                            </form>
                                            <button class="btn btn-sm btn-success" onclick="transferBalance(<?php echo $wallet['id']; ?>, '<?php echo $wallet['user_id']; ?>')">Transfer</button>
                                            <button class="btn btn-sm btn-danger" onclick="deductBalance(<?php echo $wallet['id']; ?>, '<?php echo $wallet['user_id']; ?>')">Deduct</button>
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

<!-- Add Wallet Modal -->
<div class="modal fade" id="AddWalletModal" tabindex="-1" aria-labelledby="AddWalletModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="AddWalletModalLabel">Add Wallet</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="POST" action="">
          <div class="mb-3">
            <label for="user_id" class="form-label">User</label>
            <select class="form-control" id="user_id" name="user_id" required>
              <?php
                $users_query = "SELECT id, firstname, lastname FROM users ORDER BY firstname ASC";
                $users_result = mysqli_query($conn, $users_query);
                while($user = mysqli_fetch_assoc($users_result)) {
                    echo "<option value='{$user['id']}'>{$user['firstname']} {$user['lastname']}</option>";
                }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="balance" class="form-label">Balance</label>
            <input type="number" class="form-control" id="balance" name="balance" required>
          </div>
          <button type="submit" name="add_wallet" class="btn btn-primary">Add Wallet</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit Wallet Modal -->
<div class="modal fade" id="editWalletModal" tabindex="-1" aria-labelledby="editWalletModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editWalletModalLabel">Edit Wallet</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="POST" action="">
          <input type="hidden" id="edit_id" name="id">
          <div class="mb-3">
            <label for="edit_user_id" class="form-label">User</label>
            <select class="form-control" id="edit_user_id" name="user_id" required>
              <?php
                $users_query = "SELECT id, firstname, lastname FROM users ORDER BY firstname ASC";
                $users_result = mysqli_query($conn, $users_query);
                while($user = mysqli_fetch_assoc($users_result)) {
                    echo "<option value='{$user['id']}'>{$user['firstname']} {$user['lastname']}</option>";
                }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_balance" class="form-label">Balance</label>
            <input type="number" class="form-control" id="edit_balance" name="balance" required>
          </div>
          <button type="submit" name="update_wallet" class="btn btn-primary">Update Wallet</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Transfer Balance Modal -->
<div class="modal fade" id="transferBalanceModal" tabindex="-1" aria-labelledby="transferBalanceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="transferBalanceModalLabel">Transfer Balance</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="POST" action="">
          <input type="hidden" id="transfer_id" name="id">
          <div class="mb-3">
            <label for="transfer_user_id" class="form-label">User</label>
            <select class="form-control" id="transfer_user_id" name="user_id" required>
              <?php
                $users_query = "SELECT id, firstname, lastname FROM users ORDER BY firstname ASC";
                $users_result = mysqli_query($conn, $users_query);
                while($user = mysqli_fetch_assoc($users_result)) {
                    echo "<option value='{$user['id']}'>{$user['firstname']} {$user['lastname']}</option>";
                }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="transfer_amount" class="form-label">Amount</label>
            <input type="number" class="form-control" id="transfer_amount" name="amount" required>
          </div>
          <button type="submit" name="transfer_balance" class="btn btn-primary">Transfer Balance</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Deduct Balance Modal -->
<div class="modal fade" id="deductBalanceModal" tabindex="-1" aria-labelledby="deductBalanceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deductBalanceModalLabel">Deduct Balance</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="POST" action="">
          <input type="hidden" id="deduct_id" name="id">
          <div class="mb-3">
            <label for="deduct_wallet_id" class="form-label">Wallet</label>
            <select class="form-control" id="deduct_wallet_id" name="wallet_id" required>
              <?php
                $wallets_query = "SELECT id, user_id FROM wallets ORDER BY id ASC";
                $wallets_result = mysqli_query($conn, $wallets_query);
                while($wallet = mysqli_fetch_assoc($wallets_result)) {
                    echo "<option value='{$wallet['id']}'>Wallet ID: {$wallet['id']}</option>";
                }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="deduct_amount" class="form-label">Amount</label>
            <input type="number" class="form-control" id="deduct_amount" name="amount" required>
          </div>
          <button type="submit" name="deduct_balance" class="btn btn-primary">Deduct Balance</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Initialize DataTables -->
<script>
  $(document).ready(function() {
    $('#wallets').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true
    });
  });

  function editWallet(id, user_id, balance) {
    $('#edit_id').val(id);
    $('#edit_user_id').val(user_id);
    $('#edit_balance').val(balance);
    $('#editWalletModal').modal('show');
  }

  function transferBalance(id, user_id) {
    $('#transfer_id').val(id);
    $('#transfer_user_id').val(user_id);
    $('#transferBalanceModal').modal('show');
  }

  function deductBalance(id, user_id) {
    $('#deduct_id').val(id);
    $('#deduct_wallet_id').val(user_id);
    $('#deductBalanceModal').modal('show');
  }
</script>
</body>
</html>