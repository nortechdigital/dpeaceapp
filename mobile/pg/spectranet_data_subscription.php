<?php
// session_start();
include_once './_/conn.php';
include_once './_/ac_config.php';

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

$user_id = $_SESSION['user_id'];

// Set provider from URL parameter, default to an empty string if not set
$provider = isset($_GET['provider']) ? $_GET['provider'] : '';

// Define provider details
$providers = [
    'etisalat-data' => [
        'name' => '9mobile',
        'logo' => 'img/logo/9moble_logo.png',
        'id'  => '4',
        'description' => '9mobile data subscription plans.'
    ],
    'airtel-data' => [
        'name' => 'AIRTEL',
        'logo' => 'img/logo/airtel_logo.png',
        'id'  => '2',
        'description' => 'Airtel data subscription plans.'
    ],
    'glo-data' => [
        'name' => 'Glo',
        'logo' => 'img/logo/glo_logo.jpg',
        'id'  => '3',
        'description' => 'Glo data subscription plans.'
    ],
    'mtn-data' => [
        'name' => 'MTN',
        'logo' => 'img/logo/mtn_logo.png',
        'id'  => '1',
        'description' => 'MTN data subscription plans.'
    ],
    'smile-direct' => [
        'name' => 'Smile',
        'logo' => 'img/logo/smile_logo.jpg',
        'description' => 'Smile Network subscription plans.'
    ],
    'spectranet' => [
        'name' => 'Spectranet',
        'logo' => 'img/logo/spectranet_logo.jpg',
        'description' => 'Spectranet subscription plans.'
    ],
    'swift' => [
        'name' => 'Swift',
        'logo' => 'img/logo/swift_logo.jpg',
        'description' => 'Swift Network subscription plans.'
    ]
];

// Get the selected provider details
$providerDetails = isset($providers[$provider]) ? $providers[$provider] : null;
$provider = $providerDetails['name'];
// $provider_id = $providerDetails['id'];


?>

<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php"; ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
        <button onclick="history.back()" class="btn btn-primary mb-3">Back</button>
        <?php if ($providerDetails): ?>
            <div class="card shadow p-4">
                <img src="<?php echo $providerDetails['logo']; ?>" alt="<?php echo $providerDetails['name']; ?> Logo" class="img-fluid mb-3 d-block mx-auto" width="80px" height="auto">
                <h2 class="text-center bg-primary text-light h5"><?php echo $providerDetails['name']; ?> SUBSCRIPTION</h2>

                <div class="my-5 text-center d-none d-lg-block">
                    <h2 class="text-primary">SPECTRANET DISCOUNT!</h2>
                    <h3>Pay directly and get <span class="h2 text-danger fw-2">7%</span> off.</h3>
                    <h4>Contact our customer care. <br>Call: <span class="h3 fw-3 text-primary">07045289085</span> <br> or <br><a href="https://wa.me/2347045289085" target="_blank" class="h3 fw-3 text-primary">Chat us on WhatsApp</a> <br> for more information</h4>
                </div>

                <div class="my-5 text-center d-lg-none ">
                    <h3 class="text-primary">SPECTRANET DISCOUNT!</h3>
                    <h3>Pay directly and get <span class="h2 text-danger fw-2">7%</span> off.</h3>
                    <h4>Contact our customer care: <br> Call: <span class="h3 fw-3 text-primary">07045289085</span> <br> or <br>
                        <a href="https://wa.me/2347045289085" target="_blank" class="h3 fw-3 text-primary">Chat us on WhatsApp</a> <br>
                        for more information.
                    </h4>
                </div>
                
            </div>
        <?php endif; ?>
    </div>
  </div>
</div>