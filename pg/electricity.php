<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

// require_once './artx/ArtxApiClient.php';
// $api = new ArtxApiClient(false, 'testUser1', 'Test123!');
// $electricityOperators = $api->getOperators(null, 3, '8.1'); // 8.1 = Electricity
?>
<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
      <h2 class="text-center bg-primary text-light h5">ELECTRICITY SUBSCRIPTION</h2>
      <div class="row py-3">

        <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/ekedc.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">
              <a href="?page=electricity_subscription&provider=eko" class="card-body bg-transparent text-dark text-center text-decoration-none">
              </a>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/ikeja.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">
              <a href="?page=electricity_subscription&provider=ikeja" class="card-body bg-transparent text-dark text-center text-decoration-none">
              </a>
          </div>
        </div>
        
        <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/aedc.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">
              <a href="?page=electricity_subscription&provider=abuja" class="card-body bg-transparent text-dark text-center text-decoration-none">
              </a>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/benin_logo.jpeg'); background-size: contain; background-position: center; background-repeat: no-repeat;">
              <a href="?page=electricity_subscription&provider=benin" class="card-body bg-transparent text-dark text-center text-decoration-none">
              </a>
          </div>
        </div>      

        <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/enugu_logo.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">
              <a href="?page=electricity_subscription&provider=enugu" class="card-body bg-transparent text-dark text-center text-decoration-none">
              </a>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/ibedc.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">
              <a href="?page=electricity_subscription&provider=ibadan" class="card-body bg-transparent text-dark text-center text-decoration-none">
              </a>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/jos.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">
              <a href="?page=electricity_subscription&provider=jos" class="card-body bg-transparent text-dark text-center text-decoration-none">
              </a>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/kaduna.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">
              <a href="?page=electricity_subscription&provider=kaduna" class="card-body bg-transparent text-dark text-center text-decoration-none">
              </a>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/kedco.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">
              <a href="?page=electricity_subscription&provider=kano" class="card-body bg-transparent text-dark text-center text-decoration-none">
              </a>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/phedc.png'); background-size: contain; background-position: center; background-repeat: no-repeat;">
              <a href="?page=electricity_subscription&provider=port_harcourt" class="card-body bg-transparent text-dark text-center text-decoration-none">
              </a>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card mb-3 shadow" style="height:80px; background-image: url('img/logo/yola_logo.jpeg'); background-size: contain; background-position: center; background-repeat: no-repeat;">
              <a href="?page=electricity_subscription&provider=yola" class="card-body bg-transparent text-dark text-center text-decoration-none">
              </a>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>