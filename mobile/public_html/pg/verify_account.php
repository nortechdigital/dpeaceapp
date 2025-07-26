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
      <h2 class="text-center bg-primary text-light h5">VERIFY ACCOUNT</h2>
      <div class="row py-3">
        <div class="col-md-8 offset-md-2">
          <div class="card shadow p-4">
            <form action="./_/ac_verify_account.php" method="post" class="card p-3" style="max-width:500px;margin:auto">
              <div class="mb-2">
                <label for="bvn" class="form-label">Enter Bank Verification Number (BVN)</label>
                <input type="text" name="bvn" id="bvn" class="form-control" required>
              </div>
              <div class="mb-2">
                <label for="dateofbirth" class="form-label">Enter Date of Birth</label>
                <input type="date" name="dateofbirth" id="dateofbirth" class="form-control" required>
              </div>
              <div class="d-grid mb-2">
                <button type="submit" class="btn btn-primary">VERIFY</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>