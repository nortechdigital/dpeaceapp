<?php
 ini_set('display_errors', 1);
 ini_set('display_startup_errors', 1);
 error_reporting(E_ALL);
    include "../conn.php";
    // session_start(); // Ensure session management is active
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        // If not logged in, redirect to the login page
        header("Location: ./?page=login");
        exit;
    }
    // Query to get the most recent wallet entries
    $wallet_query = "SELECT w.id, u.firstname, u.lastname, w.balance, w.user_id 
    FROM wallets w 
    JOIN users u ON w.user_id = u.id 
    WHERE w.id = (SELECT MAX(id) FROM wallets WHERE user_id = u.id) 
    ORDER BY u.firstname ASC";
    $wallet_result = mysqli_query($conn, $wallet_query);

    //get user details user table by wallet id
    $user_query = "SELECT * FROM users WHERE id = " . $_SESSION['user_id'];
    $user_result = mysqli_query($conn, $user_query);


    // Update wallet balance
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $wallet_id = $_POST['wallet_id'];
        $amount = $_POST['amount'];

        if ($action == 'topup') {
            $update_query = "UPDATE wallets SET balance = balance + $amount WHERE id = $wallet_id";
            mysqli_query($conn, $update_query);
            $_SESSION['message'] = "Wallet Topped-up Successfully!";
        } elseif ($action == 'deduction') {
            $update_query = "UPDATE wallets SET balance = balance - $amount WHERE id = $wallet_id";
            mysqli_query($conn, $update_query);
            $_SESSION['message'] = "Wallet Deducted Successfully!";
        }
    
        $wallet_query = "SELECT u.firstname, u.lastname, u.phone 
                 FROM wallets w 
                 JOIN users u ON w.user_id = u.id 
                 WHERE w.id = $wallet_id";
        $wallet_result = mysqli_query($conn, $wallet_query);
        $wallet_data = mysqli_fetch_assoc($wallet_result);
        $user_id = $_SESSION['user_id'];
        $fullname = $wallet_data['firstname'] . ' ' . $wallet_data['lastname'];
        $admin_name = $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];
        $phone_number = $wallet_data['phone'];
    	$transaction_ref = date('YmdHis');
    	$product_description = "Wallet $action by $admin_name";
    	$type = "Wallet $action";
    	$detail = ucfirst($action) . ' ' . $amount;
    	$insert_query = "INSERT INTO transactions (user_id, fullname, phone_number, transaction_ref, product_description, amount, status, profit, type, detail)
    	VALUES ($user_id, '$fullname', '$phone_number', '$transaction_ref', '$product_description', '$amount', 'success', '0', '$type', '$detail')";
    	if (mysqli_query($conn, $insert_query)) {
            $_SESSION['message'] .= "<br>Transaction logged successfully!";
        } else {
            $_SESSION['message'] .= "<br>Error logging transaction: " . mysqli_error($conn);
        }   
    
        header("Location: ./?page=wallet");
        exit;
    }
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<div class="row">
    <div class="col-lg-2">
        <?php include "./inc/sidebar.php" ?>
    </div>
    <div class="col-lg-10">
        <div class="container py-2">
            <div class="row">
                <div class="col-lg-12">
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['message']); ?>
                    <?php endif; ?>
                </div>
            </div>
            <h2 class="text-center bg-primary text-light h5 mb-5 py-2">Wallet Management</h2>
            <div class="row g-3">
                <div class="row mt-3">
                    <div class="col-lg-12">
                        <!-- <button type="button" class="btn btn-primary float-right mb-3" data-toggle="modal" data-target="#AddWalletModal">Add Wallet</button> -->
                        
                        <table id="wallets" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>User Name</th>
                                    <th>Balance</th>
                                    <th class="float-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sn=0; while($wallet = mysqli_fetch_assoc($wallet_result)): $sn += 1; ?>
                                    <tr>
                                        <td><?php echo $sn; ?></td>
                                        <td><?php echo $wallet['firstname'] . ' ' . $wallet['lastname']; ?></td>
                                        <td>&#8358;<?php echo number_format($wallet['balance'], 2); ?></td>
                                        <td class="float-right">
                                            <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#TopupWalletModal<?php echo $wallet['id']; ?>">Topup</button>
                                            <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#DeductWalletModal<?php echo $wallet['id']; ?>">Deduct</button>
                                        </td>
                                    </tr>

                                    <!-- Topup Wallet Modal -->
                                    <div class="modal fade" id="TopupWalletModal<?php echo $wallet['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="TopupWalletModalLabel<?php echo $wallet['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header justify-content-between">
                                                    <h5 class="modal-title" id="TopupWalletModalLabel<?php echo $wallet['id']; ?>">Topup Wallet</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="topup">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="wallet_id" value="<?php echo $wallet['id']; ?>">
                                                        <div class="form-group">
                                                            <label for="amount">Amount</label>
                                                            <input type="number" class="form-control" name="amount" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Topup</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Deduct Wallet Modal -->
                                    <div class="modal fade" id="DeductWalletModal<?php echo $wallet['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="DeductWalletModalLabel<?php echo $wallet['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header justify-content-between">
                                                    <h5 class="modal-title" id="DeductWalletModalLabel<?php echo $wallet['id']; ?>">Deduct Wallet</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="deduction">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="wallet_id" value="<?php echo $wallet['id']; ?>">
                                                        <div class="form-group">
                                                            <label for="amount">Amount</label>
                                                            <input type="number" class="form-control" name="amount" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-danger">Deduct</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>