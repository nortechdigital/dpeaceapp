<?php
if (!isset($_SESSION['user_id'])) {
  header("Location: ./?page=login");
  exit;
}
?>

<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">  
    <div class="container py-2">
      <div class="card shadow p-4">
        <div class="text-center mb-3">
          <!-- <img src="img/logo/9moble_logo.png" alt="9mobile" class="img-fluid mx-2" width="80px" height="auto">
          <img src="img/logo/airtel_logo.png" alt="Airtel" class="img-fluid mx-2" width="80px" height="auto">
          <img src="img/logo/glo_logo.jpg" alt="Glo" class="img-fluid mx-2" width="80px" height="auto">
          <img src="img/logo/mtn_logo.png" alt="MTN" class="img-fluid mx-2" width="80px" height="auto"> -->
        </div>
        <h2 class="text-center bg-primary text-light h5">Exam PIN</h2>
        <form action="" method="POST">
          <div class="form-group">
            <label for="provider">Select Provider</label>
            <select name="provider" id="provider" class="form-control">
              <option value="">Select Provider</option>
              <option value="9mobile">WAEC</option>
              <option value="airtel">NECO</option>
              <option value="glo">JAMB</option>
              <option value="mtn">NABTEB</option>
            </select>
          </div>
          <div class="form-group mt-3">
            <label for="type">Select Type</label>
            <select name="type" id="type" class="form-control">
              <option value="">Select Type</option>
              <option value="9mobile">Registration PIN</option>
              <option value="airtel">Result</option>
              <option value="glo">JAMB</option>
              <option value="mtn">NABTEB</option>
            </select>
          </div>
          <div class="form-group mt-3">
            <label for="phone_number">Phone Number</label>
            <input type="text" name="phone_number" id="phone_number" class="form-control" placeholder="Enter Phone Number">
          </div>
          <div class="form-group mt-3">
            <label for="amount">Amount</label>
            <input type="number" name="amount" id="amount" class="form-control" placeholder="Enter Amount">
          </div>
          <div class="form-group mt-3 text-center">
            <?php if ($status == 0): ?>
                <a href="./?page=verify_account" class="btn btn-primary">Verify Account</a>
            <?php else: ?>
              <button type="submit" class="btn btn-primary">Buy</button>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<select name="type" id="type" class="form-control">
              <option value="">Select Type</option>
              <option value="9mobile">Registration PIN</option>
              <option value="airtel">Result</option>
              <option value="glo">JAMB</option>
              <option value="mtn">NABTEB</option>
            </select>