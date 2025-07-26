<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
    echo $_SESSION['role'];
    
}
?>
<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
      <h2 class="text-center bg-primary text-light h5">DASHBOARD</h2>
      <div class="">
        <p class="h5 text-primary text-center">🎉 Enjoy <span class="text-danger h2">7%</span> OFF your Spectranet subscription! <br>No need to fund your wallet. Kindly follow the below step: <br>Tap the Data subscription page to access the Spectranet data link.</p>
      </div>
      <hr>
      <div class="row g-3">
        <div class="col-lg-3">
          <div class="card mb-3 shadow" style="height:130px">
              <a href="./?page=profile" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                <img src="img/icons/person.svg" alt="" class="img-fluid mt-2" style="width:90px;height:60px;object-fit:fill">
              <h5>PROFILE</h5>
              </a>
          </div>
        </div>
        <div class="col-lg-3">
          <div class="card mb-3 shadow" style="height:130px">
              <a href="./?page=airtime" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                <img src="img/icons/phone.svg" alt="" class="img-fluid mt-2" style="width:90px;height:60px;object-fit:fill">
              <h5>AIRTIME RECHARGE</h5>
              </a>
          </div>
        </div>
        <div class="col-lg-3">
          <div class="card mb-3 shadow" style="height:130px">
              <a href="./?page=data" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                <img src="img/icons/router.svg" alt="" class="img-fluid mt-2" style="width:90px;height:60px;object-fit:fill">
              <h5>DATA SUBSCRIPTION</h5>
              </a>
          </div>
        </div>
        <div class="col-lg-3">
          <div class="card mb-3 shadow" style="height:130px">
              <a href="./?page=electricity" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                <img src="img/icons/lightning-charge.svg" alt="" class="img-fluid mt-2" style="width:90px;height:60px;object-fit:fill">
              <h5>ELECTRICITY</h5>
              </a>
          </div>
        </div>
        <div class="col-lg-3">
          <div class="card mb-3 shadow" style="height:130px">
              <a href="./?page=cabletv" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                <img src="img/icons/tv.svg" alt="" class="img-fluid mt-2" style="width:90px;height:60px;object-fit:fill">
              <h5>CABLE TV</h5>
              </a>
          </div>
        </div>
        <div class="col-lg-3">
          <div class="card mb-3 shadow" style="height:130px">
              <a href="./?page=wallet" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                <img src="img/icons/wallet2.svg" alt="" class="img-fluid mt-2" style="width:90px;height:60px;object-fit:fill">
              <h5>Wallet</h5>
              </a>
          </div>
        </div>
        <div class="col-lg-3">
          <div class="card mb-3 shadow" style="height:130px">
              <a href="./?page=transactions" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                <img src="img/icons/clipboard-check.svg" alt="" class="img-fluid mt-2" style="width:90px;height:60px;object-fit:fill">
              <h5>Transaction History</h5>
              </a>
          </div>
        </div>
        <div class="col-lg-3">
          <div class="card mb-3 shadow" style="height:130px">
              <a href="./?page=settings" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                <img src="img/icons/tools.svg" alt="" class="img-fluid mt-2" style="width:90px;height:60px;object-fit:fill">
              <h5>Settings</h5>
              </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Button trigger modal -->
<button type="button" class="btn btn-sm btn-white" data-bs-toggle="modal" data-bs-target="#exampleModal"></button>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5 text-danger" id="exampleModalLabel">Notice!</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body mt-3">
        <p class="h2 text-primary">Our Mobile Application will be available starting August 20th, 2025.</p>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    setTimeout(function() {
      var btn = document.querySelector('[data-bs-toggle="modal"][data-bs-target="#exampleModal"]');
      if(btn) btn.click();
    }, 5000);
  });
</script>