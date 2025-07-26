<?php 
// session_start();
 $otp = isset($_SESSION['pre_user']['otp']) ? $_SESSION['pre_user']['otp'] : 'no otp found!';
echo $output = '  <h5>An OTP has been sent to the provided Email. </h5>';
?>
  <!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> -->
  <script>
    function checkOtp() {
      const otpInput = document.getElementById('otp_code').value;
      const sessionOtp = '<?php echo $otp; ?>';
      const submitButton = document.getElementById('submit_button');
      if (otpInput === sessionOtp) {
        submitButton.disabled = false;
      } else {
        submitButton.disabled = true;
      }
    }
  </script>
<section class="vh-100 d-flex align-items-center">
  <div class="container py-5">
    <div class="row justify-content-center">
      <!-- Left Side Image -->
      <div class="col-lg-6 d-none d-lg-block text-center">
        <img src="./img/dpeace-app.png" alt="Site Logo" class="img-fluid" style="max-width: 100%; height: auto;">
      </div>

      <!-- Login Form -->
      <div class="col-lg-5">
        <form action="./_/ac_create_account.php" method="post" class="card shadow-lg">
          <div class="card-body p-4">
            <div class="mb-3 text-center">
              <h1 class="h3 fw-bold"><u>Verify OTP</u></h1>
            </div>

            <!-- OTP Field -->
            <div class="mb-3">
              <input type="number" name="otp_code" id="otp_code" class="form-control" placeholder="Enter OTP" required oninput="checkOtp()">
            </div>

            <!-- Verify Button -->
            <div class="d-grid">
              <button type="submit" id="submit_button" class="btn btn-primary" disabled>Verify</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
</body>
</html>