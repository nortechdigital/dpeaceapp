<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['user_id'])) {
  header("Location: ./?page=login");
  exit;
}
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
?>

<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
      <h2 class="text-center bg-primary text-light h5">FUND WALLET</h2>
    <div class="row py-3">
      <div class="col-md-8 offset-md-2">
        <div class="card shadow p-4">
          <h6>Enter Amount to Fund</h6>
          <form id="paymentForm">
            <div class="mb-3">
              <label for="amount" class="form-label">Amount (&#8358;)</label>
              <input type="number" id="amount" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user_email); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Pay Now</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

  <script>
    const paymentForm = document.getElementById('paymentForm');
    paymentForm.addEventListener('submit', payWithPaystack, false);

    function payWithPaystack(e) {
      e.preventDefault();

      let handler = PaystackPop.setup({
        key: 'your-public-key-here', // Replace with your Paystack public key
        email: document.getElementById('email').value,
        amount: document.getElementById('amount').value * 100, // Amount in kobo
        currency: 'NGN',
        ref: '' + Math.floor((Math.random() * 1000000000) + 1), // Generate a random reference number
        callback: function(response) {
          // This happens after the payment is completed successfully
          let message = 'Payment complete! Reference: ' + response.reference;
          alert(message);
          // You can make an AJAX call here to update the user's wallet balance in your database
        },
        onClose: function() {
          alert('Transaction was not completed, window closed.');
        },
      });

      handler.openIframe();
    }
  </script>

<script src="https://js.paystack.co/v1/inline.js"></script>