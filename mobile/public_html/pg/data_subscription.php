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

// Fetch wallet balance
$query = "SELECT balance FROM wallets WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($balance);
$stmt->fetch();
$stmt->close();

// Set provider from URL parameter, default to an empty string if not set
echo $provider = isset($_GET['provider']) ? $_GET['provider'] : '';

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
$provider_id = $providerDetails['id'];

// Fetch data bundle from data_plan table
$query = "SELECT * FROM data_plans WHERE provider = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $provider);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
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
                <form action="./_/ac_process_subscription.php" method="post">
                    <input type="hidden" name="provider" value="<?php echo $provider; ?>">
                    <input type="hidden" name="network" value="<?php echo $provider_id; ?>">
                    <input type="hidden" name="balance" value="<?php echo $balance; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    <input type="text" name="phone_number" id="phone_number" class="form-control mt-3" placeholder="Enter Phone Number" required>
                    <div id="number-verification" class="mt-2"></div>
                    
                    <select name="data_plan" id="data_plan" class="form-control mt-3">
                        <option value="">Select Data Plan</option>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <option value="<?php echo $row['plan_id']; ?>" data-price="<?php echo $row['price']; ?>"><?php echo $row['plan_name']; ?> - &#8358;<?php echo number_format($row['price'], 2); ?> (Validity: <?php echo $row['validity']; ?> days)</option>
                        <?php endwhile; ?>
                    </select>
                    
                    <div class="form-group mt-3">
                        <label for="ported_number">Is this a ported number?</label>
                        <div class="form-check">
                        <input class="form-check-input" type="radio" name="ported_number" id="ported_number_yes" value="true">
                        <label class="form-check-label" for="ported_number_yes">
                            Yes
                        </label>
                        </div>
                        <div class="form-check">
                        <input class="form-check-input" type="radio" name="ported_number" id="ported_number_no" value="false" checked>
                        <label class="form-check-label" for="ported_number_no">
                            No
                        </label>
                        </div>
                    </div>
                    <hr>
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-3">Bundle Price: &#8358;<span name="price" id="price">0</span></div>
                            <?php if ($provider == 'N'): ?>
                                <div class="col-2">Bonus: 1.5%</div>
                                <?php $bonus = 0.015; ?>
                            <?php elseif ($provider == 'Glo'): ?>
                                <div class="col-2">Bonus:4%</div>
                                <?php $bonus = 0.04; ?>
                            <?php endif ?>
                                <?php else: ?>
                                <div class="col-2">Bonus: 2%</div>
                                <?php $bonus = 0.02; ?>
                            <?php endif ?>
                            <div class="col-4">Discount Received: &#8358;<span name="profit" id="profit">0</span></div>
                            <div class="col-3">Amount to Pay: &#8358;<span name="discounted_price" id="discounted_price">0</span></div>
                            <input type="text" name="discounted_price" id="hidden_discounted_price" value="0">
                        </div>
                    </div>
                    <hr>
                    <div class="form-group mt-3 text-center">
                        <?php if ($status == 0): ?>
                            <a href="./?page=verify_account" class="btn btn-primary">Verify Account</a>
                        <?php else: ?>
                            <button type="submit" class="btn btn-primary btn-block mt-3">Purchase Bundle</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">Invalid provider selected.</div>    
        <?php endif; ?>
    </div>
  </div>
</div>

<script>
    document.getElementById('data_plan').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = parseFloat(selectedOption.getAttribute('data-price'));
        const discount = price * <?php echo $bonus; ?>;
        const discountedPrice = price - discount;
        const profit = discount;

        document.getElementById('price').innerText = price.toFixed(2);
        document.getElementById('discounted_price').innerText = discountedPrice.toFixed(2);
        document.getElementById('profit').innerText = profit.toFixed(2);
    });

    document.addEventListener('DOMContentLoaded', function () {
  const discountedPriceDisplay = document.getElementById('discounted_price');
  const hiddenDiscountedPrice = document.getElementById('hidden_discounted_price');

  const updateHiddenDiscountedPrice = () => {
    hiddenDiscountedPrice.value = discountedPriceDisplay.textContent.trim();
  };

  const observer = new MutationObserver(updateHiddenDiscountedPrice);
  observer.observe(discountedPriceDisplay, { childList: true, subtree: true });

  updateHiddenDiscountedPrice(); // Initialize the value on load
  });
</script>