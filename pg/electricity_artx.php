<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

require_once './artx/ArtxApiClient.php';
$api = new ArtxApiClient(true, 'dpeaceapp.api.ngn', 'login@DPeaceAdmin1234');
$electricityOperators = $api->getOperators(null, 3, '8.1');
print_r($electricityOperators);
?>
<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
      <h2 class="text-center bg-primary text-light h5">ELECTRICITY SUBSCRIPTION</h2>
      <div class="row py-3">

        <?php foreach ($electricityOperators as $operator): ?>
        <div class="col-md-3 d-none">
          <div class="card mb-3 shadow">
              <a href="?page=electricity_subscription_artx&id=<?= $operator['id'] ?>&name=<?= $operator['name'] ?>" class="card-body bg-transparent text-dark text-center text-decoration-none">
                <?= $operator['name'] ?>
              </a>
          </div>
        </div>
        <?php endforeach; ?>

        
      </div>
    </div>
  </div>
</div>