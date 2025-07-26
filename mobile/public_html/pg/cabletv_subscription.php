<?php
// session_start();
include_once './_/conn.php';
include_once './_/ac_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

// Initialize $status variable
$status = isset($_SESSION['status']) ? $_SESSION['status'] : 0;

$providers = [
    'dstv' => [
        'id' => 'DSTV',
        'name' => 'DStv',
        'logo' => 'img/logo/dstv.png',
        'bonus' => '1.75%',
        'description' => 'DStv subscription plans.'
    ],
    'gotv' => [
        'id' => 'GOTV',
        'name' => 'GOtv',
        'logo' => 'img/logo/gotv.png',
        'bonus' => '1.75%',
        'description' => 'GOtv subscription plans.'
    ],
    'startimes' => [
        'id' => 'STARTIMES',
        'name' => 'Startimes',
        'logo' => 'img/logo/startimes.png',
        'bonus' => '1.80%',
        'description' => 'Startimes subscription plans.'
    ],
    // Add other providers here...
];

// Bonus percentage mapping
$bonusMapping = [
    'DSTV' => 1.75,
    'GOTV' => 1.75,
    'STARTIMES' => 1.80
];

$provider = isset($_GET['provider']) ? $_GET['provider'] : null;
$providerDetails = isset($providers[$provider]) ? $providers[$provider] : null;

if ($providerDetails) {
    $biller_id = $providerDetails['id'];
} else {
    $errorMessage = "Provider not found.";
    $biller_id = null;
}

// Fetch available TV bouquets from API
if ($biller_id) {
    $endpoint = VAS2NETS_BASE_URL . VAS2NETS_ENV_URI . "bouquet/tv/" . $biller_id;
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode(VAS2NETS_USERNAME . ':' . VAS2NETS_PASSWORD)
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $plans = [];
    if ($httpCode == 200) {
        $responseData = json_decode($response, true);
        $plans = $responseData['data']['bouquets'] ?? [];
    // } elseif ($httpCode == 401) {
    //     $errorMessage = "Invalid API credentials. Please contact support.";
    // } elseif ($httpCode == 403) {
    //     $errorMessage = "Access denied. Please check your permissions.";
    // } else {
    //     $errorMessage = "An error occurred while fetching subscription plans. Please try again later.";
     }
} else {
    $plans = [];
}



?>



<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php"; ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
        <button onclick="history.back()" class="btn btn-secondary mb-3">Back</button>
        <?php if ($providerDetails): ?>
            <div class="card shadow p-4">
            <img src="<?php echo $providerDetails['logo']; ?>" alt="<?php echo $providerDetails['name']; ?> Logo" class="img-fluid mb-3 d-block mx-auto" width="80px" height="auto">
            <h2 class="text-center bg-primary text-light h5"><?php echo $providerDetails['name']; ?> Subscription</h2>
            <form action="./_/ac_process_tv_subscription.php" method="post" id="cabletvForm">
                <input type="hidden" name="provider" value="<?php echo $provider; ?>">
                <input type="hidden" name="biller_id" id="biller_id" value="<?php echo $biller_id; ?>">
                <input type="text" name="smartcard_number" id="smartcard_number" class="form-control mt-3" placeholder="Enter IUC or Smart Card Number" required>
                
                <?php if (!empty($plans)): ?>
                <div class="form-group my-3">
                    <label for="bundle" class="font-weight-bold">Select Bundle</label>
                    <select name="bouquet_code" id="bouquet_code" class="form-select" required>
                        <option value="" selected disabled>-- Select a package --</option>
                        <?php foreach ($plans as $plan): ?>
                            <option value="<?php echo htmlspecialchars($plan['code']); ?>" 
                                    data-price="<?php echo htmlspecialchars($plan['price']); ?>">
                                <?php echo htmlspecialchars($plan['name']); ?> - 
                                ₦<?php echo number_format($plan['price'], 2); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="hidden" name="selected_price" id="selected_price" value="">
                    
                </div>
            <?php else: ?>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle"></i> No bundles currently available for this provider.
                    Please check back later or try another provider.
                </div>
            <?php endif; ?>
            <hr>
            <div class="mt-3">
                <div class="row">
                    <div class="col-lg-3">Cost Price: &#8358;<span name="price" id="price">0</span></div>
                    <div class="col-lg-2">Bonus: <span><?php echo isset($bonusMapping[$biller_id]) ? $bonusMapping[$biller_id] . '%' : '0%'; ?></span></div>
                    <div class="col-lg-4">Discount Received: &#8358;<span name="profit" id="profit">0</span></div>
                    <div class="col-lg-3">Amount to Pay: &#8358;<span name="discounted_price" id="discounted_price">0</span></div>
                </div>
                <input type="hidden" name="discounted_price" id="hidden_discounted_price" value="0">
            </div>


            <hr>
                
                    <button type="button" class="btn btn-primary mt-3 w-100" id="confirmButton">Submit</button>
              
            </form>

            <!-- Modal -->
            <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                            <div class="modal-header justify-content-center">
                                <h5 class="modal-title">Confirm Transaction</h5>
                            </div>
                            <div class="modal-body justify-content-between">
                                <p>Provider: <strong><?php echo $providerDetails['name']; ?></strong></p>
                                <p>Smartcard Number: <strong><span id="modalSmartcardNumber"></span></strong></p>
                                <p>Selected Plan: <strong><span id="modalSelectedPlan"></span></strong></p>
                                <p>Cost Price: <strong>&#8358;<span id="modalPrice">0</span></strong></p>
                                <p>Discount Received: <strong>&#8358;<span id="modalDiscount"></span></strong></p>
                                <p>Amount to Pay: <strong>&#8358;<span id="modalAmountToPay"></span></strong></p>
                                <p class="text-danger">Are you sure you want to proceed with this transaction?</p>

                            </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-primary" id="modalSubmitButton">OK</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="document.getElementById('confirmationModal').classList.remove('show'); document.body.classList.remove('modal-open'); document.querySelector('.modal-backdrop').remove();">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>

            

            </div>
        <?php else: ?>
            <div class="alert alert-danger text-center">
                <?php echo isset($errorMessage) ? $errorMessage : "Provider not found."; ?>
            </div>
        <?php endif; ?>
    </div>
  </div>
</div>

<script>
    document.getElementById('bouquet_code').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const selectedPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const bonusPercentage = <?php echo isset($bonusMapping[$biller_id]) ? $bonusMapping[$biller_id] : 0; ?>; // Dynamic bonus percentage
        const discount = (selectedPrice * bonusPercentage) / 100;
        const discountedPrice = selectedPrice - discount;

        // Update the displayed values
        document.getElementById('price').textContent = selectedPrice.toFixed(2);
        document.getElementById('profit').textContent = discount.toFixed(2);
        document.getElementById('discounted_price').textContent = discountedPrice.toFixed(2);
        document.getElementById('hidden_discounted_price').value = discountedPrice.toFixed(2);
    });

    document.getElementById('confirmButton').addEventListener('click', function () {
        // Ensure modal values are updated correctly
        document.getElementById('modalSmartcardNumber').textContent = document.getElementById('smartcard_number').value;
        const selectedOption = document.getElementById('bouquet_code').options[document.getElementById('bouquet_code').selectedIndex];
        document.getElementById('modalSelectedPlan').textContent = selectedOption.textContent.trim();
        document.getElementById('modalPrice').textContent = document.getElementById('price').textContent;
        document.getElementById('modalDiscount').textContent = document.getElementById('profit').textContent;
        document.getElementById('modalAmountToPay').textContent = document.getElementById('discounted_price').textContent;
    });

    document.getElementById('confirmButton').addEventListener('click', function () {
        const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        confirmationModal.show();
    });

    document.getElementById('modalSubmitButton').addEventListener('click', function () {
        document.getElementById('cabletvForm').submit();
    });

    document.querySelector('.btn-secondary[data-bs-dismiss="modal"]').addEventListener('click', function() {
        const modalElement = document.getElementById('confirmationModal');
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        modalInstance.hide();
    });
</script>