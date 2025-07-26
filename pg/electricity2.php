<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

require_once './artx/ArtxApiClient.php';
$api = new ArtxApiClient(true, 'dpeaceapp.api.ngn', 'login@DPeaceAdmin1234');
$electricityOperators = $api->getOperators(null, 3, '8.1'); // 8.1 = Electricity
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
        <?php
		switch ($operator['id']) {
          case 671:
        	$bg_img = 'aedc.png';
            break;
          case 2712:
        	$bg_img = 'benin_logo.jpeg';
            break;
          case 536:
        	$bg_img = 'ekedc.png';
            break;
          case 676:
        	$bg_img = 'enugu_logo.png';
            break;
          case 678:
        	$bg_img = 'ibedc.png';
            break;
          case 674:
        	$bg_img = 'ikeja.png';
            break;
          case 673:
        	$bg_img = 'jos.png';
            break;
          case 672:
        	$bg_img = 'kaduna.png';
            break;
          case 675:
        	$bg_img = 'kedco.png';
            break;
          case 677:
        	$bg_img = 'phedc.png';
            break;
          case 2713:
        	$bg_img = 'yola_logo.jpeg';
            break;
          default:
        	$bg_img = '';
        }

        if ($operator['currency'] === 'NGN'):
		?>
        <div class="col-md-3">
          <div class="card mb-3 shadow" style="background-image: url('img/logo/<?= $bg_img ?>'); background-size: contain; background-position: center; background-repeat: no-repeat;">
              <a href="?page=electricity_subscription2&id=<?= $operator['id'] ?>&name=<?= $operator['name'] ?>" class="card-body bg-transparent text-dark text-center text-decoration-none d-inline-block pt-5 pb-3">
                <span></span>
              </a>
          </div>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>     


      </div>
    </div>
  </div>
</div>