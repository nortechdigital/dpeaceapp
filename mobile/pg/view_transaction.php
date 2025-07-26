<?php
// Database connection
include_once './_/conn.php';

$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $transaction = $result->fetch_assoc();
        $fullname = $transaction['fullname'];
        $phone_number = $transaction['phone_number'];
        $product_description = $transaction['product_description'];
        $amount = $transaction['amount'];
        $created_at = $transaction['created_at'];
        $request_id = $transaction['transaction_ref'];
        $status = $transaction['status'];
    	$type = $transaction['type'];
    	$customer_name = $transaction['customer_name'];
    	$customer_address = $transaction['customer_address'];
    	$smartcard_number = $transaction['smartcard_number'];
    	$unit_purchased = $transaction['unit_purchased'];
    	$token = $transaction['token'];
    } else {
        $fullname = '--Customer Name--';
        $phone_number = '--Customer Phone--';
        $product_description = '--Product Description--';
        $amount = '0.00';
        $created_at = date('d-m-y');
        $request_id = '--Request ID--';
        $status = 'Transaction Not Found!';
    }
    $stmt->close();
} else {
    $fullname = '--Customer Name--';
    $phone_number = '--Customer Phone--';
    $product_description = '--Product Description--';
    $amount = '0.00';
    $created_at = date('d-m-y');
    $request_id = '--Request ID--';
    $status = 'Invalid Request ID!';
}
?>
<div class="row">
  <div class="col-lg">
    <div class="container">
      <div class="col-lg-6 mx-auto">
        <div class="card mb-3">
          <div class="card-body text-center">
            <img src="https://dpeaceapp.com/img/dpeace-app.png" alt="DPeace Logo" style="width: 100px; margin-bottom: 20px;">
            <h5 class="text-center">Transaction Receipt</h5>
            <h4 class="text-center text-success">Transaction Status: <?php echo ucfirst($status) ?></h4>
            <p class="small text-center fst-italic"><small><strong>Request ID:</strong> <?= $request_id ?></small></p>
            <hr>
            <table class="table text-start">
              <tr>
                <th>Date</th>
                <td><?= $created_at ?></td>
              </tr>
              <tr>
                <th>Description</th>
                <td><?= $product_description ?></td>
              </tr>
              <tr>
                <th>Customer Name</th>
                <td><?= $fullname ?></td>
              </tr>
              <tr>
                <th>Phone Number</th>
                <td><?= $phone_number ?></td>
              </tr>
              <tr>
                <th>Amount</th>
                <td>&#8358;<?= number_format($amount, 2) ?></td>
              </tr>
            </table>
            <p class="text-center mt-4 h4">Thank you for choosing the Peace App!</p>
            <hr>
            <div class="form-group mt-3 text-center">
              <a href="?page=transactions" class="btn btn-primary btn-block mt-3">Close</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>