<?php
// session_start();
include_once './_/conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

$provider = isset($_GET['provider']) ? htmlspecialchars($_GET['provider']) : '';

// Define provider details
$providers = [
    '9mobile' => [
        'name' => '9mobile',
        'logo' => 'img/logo/9moble_logo.png',
        'description' => '9mobile airtime top-up.'
    ],
    'airtel' => [
        'name' => 'Airtel',
        'logo' => 'img/logo/airtel_logo.png',
        'description' => 'Airtel airtime top-up.'
    ],
    'glo' => [
        'name' => 'Glo',
        'logo' => 'img/logo/glo_logo.jpg',
        'description' => 'Glo airtime top-up.'
    ],
    'mtn' => [
        'name' => 'MTN',
        'logo' => 'img/logo/mtn_logo.png',
        'description' => 'MTN airtime top-up.'
    ],
];

// Get provider details
$providerDetails = isset($providers[$provider]) ? $providers[$provider] : null;
?>

<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php"; ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
      <?php if ($providerDetails): ?>
        <div class="card shadow p-4">
          <img src="<?php echo file_exists($providerDetails['logo']) ? $providerDetails['logo'] : 'img/logo/default_logo.png'; ?>" alt="<?php echo $providerDetails['name']; ?> Logo" class="img-fluid mb-3 d-block mx-auto" width="80px" height="auto">
          <h2 class="text-center bg-primary text-light h5"><?php echo $providerDetails['name']; ?> AIRTIME TOPUP</h2>
          <div class="container py-2">
            <div class="card shadow p-4">
              <h2 class="text-center bg-primary text-light h5">Airtime Top-Up</h2>
              <form action="./_/ac_process_airtime.php" method="POST">
                <div class="form-group">
                  <label for="provider">Select Provider</label>
                  <select name="provider" id="provider" class="form-control">
                    <option value="">Select Provider</option>
                    <?php foreach ($providers as $key => $p): ?>
                      <option value="<?php echo $key; ?>" <?php echo $provider == $key ? 'selected' : ''; ?>><?php echo $p['name']; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group mt-3">
                  <label for="phone_number">Phone Number</label>
                  <input type="text" name="phone_number" id="phone_number" class="form-control" placeholder="Enter Phone Number" required>
                </div>
                <div class="form-group mt-3">
                  <label for="amount">Amount</label>
                  <input type="number" name="amount" id="amount" class="form-control" placeholder="Enter Amount" required>
                </div>
                <div class="form-group mt-3 text-center">
                  <button type="submit" class="btn btn-primary">Top-Up</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-danger">Invalid provider selected.</div>
      <?php endif; ?>
    </div>
  </div>
</div>
