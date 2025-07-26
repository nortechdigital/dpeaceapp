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
      <h2 class="text-center bg-primary text-light h5">DATA SUBSCRIPTION</h2>
      <div class="row py-3">

       <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/swift_logo.jpg'); background-size: contain; background-position: center; background-repeat: no-repeat;">
            <a href="?page=swift_data_subscription&provider=swift" class="card-body bg-transparent text-dark text-center text-decoration-none">
              <!-- <h5>Swift</h5> -->
            </a>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/spectranet_logo.jpg'); background-size: contain; background-position: center; background-repeat: no-repeat;">
            <a href="?page=spectranet_data_subscription&provider=spectranet" class="card-body bg-transparent text-dark text-center text-decoration-none">
              <!-- <h5>Spectranet</h5> -->
            </a>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/glo_logo.jpg'); background-size: contain; background-position: center; background-repeat: no-repeat;">
            <a href="?page=get_data_plans_vas2nets&biller_id=GLO-DATA" class="card-body bg-transparent text-dark text-center text-decoration-none">
              <!-- <h5>Glo</h5> -->
            </a>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/smile_logo.jpg'); background-size: contain; background-position: center; background-repeat: no-repeat;">
            <a href="?page=smile_data_subscription&provider=smile" class="card-body bg-transparent text-dark text-center text-decoration-none">
              <!-- <h5>Smile</h5> -->
            </a>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/mtn_logo.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">
            <a href="?page=get_data_plans_vas2nets&biller_id=MTN-DATA" class="card-body bg-transparent text-dark text-center text-decoration-none">
              <!-- <h5>MTN</h5> -->
            </a>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/9moble_logo.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">
            <a href="?page=get_data_plans_vas2nets&biller_id=9Mobile-DATA" class="card-body bg-transparent text-dark text-center text-decoration-none">
              <!-- <h5>9mobile</h5> -->
            </a>
          </div>
        </div>
        
        <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/airtel_logo.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">
            <a href="?page=get_data_plans_vas2nets&biller_id=Airtel-DATA" class="card-body bg-transparent text-dark text-center text-decoration-none">
              <!-- <h5>Airtel</h5> -->
            </a>
          </div>
        </div>
      
      </div>
    </div>
  </div>
</div>
