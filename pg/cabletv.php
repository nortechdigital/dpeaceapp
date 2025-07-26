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
      <h2 class="text-center bg-primary text-light h5">CABLE TV SUBSCRIPTION</h2>
      <div class="row py-3">
        <div class="col-md-4">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/dstv.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">
              <a href="?page=cabletv_subscription&provider=dstv" class="card-body bg-transparent text-dark text-center text-decoration-none">
              </a>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/gotv.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">
              <a href="?page=cabletv_subscription&provider=gotv" class="card-body bg-transparent text-dark text-center text-decoration-none">
              </a>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/startimes.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">
              <a href="?page=cabletv_subscription&provider=startimes" class="card-body bg-transparent text-dark text-center text-decoration-none">
              </a>
          </div>
        </div>

        <!-- Add more providers as needed -->
      </div>
    </div>
  </div>
</div>