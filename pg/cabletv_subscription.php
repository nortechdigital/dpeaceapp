<?php
session_start();
include_once './_/conn.php';
include_once './_/ac_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

$status = $_SESSION['status'] ?? 0;

// TV Providers Configuration
$providers = [
    'dstv' => [
        'id' => 'DSTV',
        'name' => 'DStv',
        'logo' => 'img/logo/dstv.png',
        'bonus' => '1.75%',
        'description' => 'DStv subscription plans'
    ],
    'gotv' => [
        'id' => 'GOTV', 
        'name' => 'GOtv',
        'logo' => 'img/logo/gotv.png',
        'bonus' => '1.75%',
        'description' => 'GOtv subscription plans'
    ],
    'startimes' => [
        'id' => 'STARTIMES',
        'name' => 'Startimes',
        'logo' => 'img/logo/startimes.png',
        'bonus' => '1.80%',
        'description' => 'Startimes subscription plans'
    ]
];

$bonusMapping = [
    'DSTV' => 1.75,
    'GOTV' => 1.75,
    'STARTIMES' => 1.80
];

// Get provider details
$provider = $_GET['provider'] ?? null;

// Validate provider parameter
if (!$provider || !isset($providers[$provider])) {
    echo '<div class="alert alert-danger text-center">';
    echo '<i class="fas fa-times-circle"></i> Provider not found. Please select a valid provider.';
    echo '</div>';
    exit;
}

$providerDetails = $providers[$provider] ?? null;
$biller_id = $providerDetails['id'] ?? null;

// Fetch bundles from API
$bundles = [];
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

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $mainPlans = $data['data']['bouquets'] ?? [];
        
        foreach ($mainPlans as $plan) {
            // Create base bundle (main plan only)
            $bundles[] = [
                'code' => $plan['code'],
                'name' => $plan['name'],
                'price' => $plan['price'],
                'items' => [
                    [
                        'code' => $plan['code'],
                        'name' => $plan['name'],
                        'price' => $plan['price'],
                        'type' => 'main'
                    ]
                ]
            ];
            
            // Create bundles with addons if available
            if (!empty($plan['addons'])) {
                foreach ($plan['addons'] as $addon) {
                    $bundles[] = [
                        'code' => $plan['code'] . '_' . $addon['code'],
                        'name' => $plan['name'] . ' + ' . $addon['name'],
                        'price' => $plan['price'] + $addon['price'],
                        'items' => [
                            [
                                'code' => $plan['code'],
                                'name' => $plan['name'],
                                'price' => $plan['price'],
                                'type' => 'main'
                            ],
                            [
                                'code' => $addon['code'],
                                'name' => $addon['name'],
                                'price' => $addon['price'],
                                'type' => 'addon'
                            ]
                        ]
                    ];
                }
            }
        }
        
        // Sort bundles by price
        usort($bundles, fn($a, $b) => $a['price'] <=> $b['price']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $providerDetails['name'] ?? 'TV Subscription'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="row">
        <div class="col-lg-2">
            <?php include "./inc/sidebar.php"; ?>
        </div>
        <div class="col-lg-10">
            <div class="container py-2">
                <button onclick="history.back()" class="btn btn-secondary mb-3">
                    <i class="fas fa-arrow-left"></i> Back
                </button>

                <?php if ($providerDetails): ?>
                    <div class="card shadow p-4">
                        <div class="text-center">
                            <img src="<?php echo $providerDetails['logo']; ?>" alt="<?php echo $providerDetails['name']; ?>" class="img-fluid mb-3" style="max-height: 80px;">
                            <h3 class="bg-primary text-light p-2 rounded"><?php echo $providerDetails['name']; ?> Subscription</h2>
                        </div>

                        <form id="subscriptionForm" action="./_/ac_process_tv_subscription.php" method="post">
                            <input type="hidden" name="provider" value="<?php echo $provider; ?>">
                            <input type="hidden" name="biller_id" value="<?php echo $biller_id; ?>">
                            <input type="hidden" name="bouquet_code" id="bouquet_code">
                            <input type="hidden" name="selected_price" id="selected_price" value="<?php echo isset($bundles[0]['price']) ? $bundles[0]['price'] : 0; ?>">
                            <input type="hidden" name="discounted_price" id="hidden_discounted_price" value="0">
                            
                            <div class="form-group mt-3">
                                <label for="smartcard_number" class="form-label">Smartcard/IUC Number</label>
                                <input type="text" name="smartcard_number" id="smartcard_number" class="form-control" placeholder="Enter your smartcard number" required>
                            </div>

                            <?php if (!empty($bundles)): ?>
                                <div class="form-group my-3">
                                    <label for="bundle_select" class="form-label fw-bold">Select Bundle</label>
                                    <select name="bundle_code" id="bundle_select" class="form-select" required>
                                        <option value="" selected disabled>-- Choose a bundle --</option>
                                        <?php foreach ($bundles as $bundle): ?>
                                            <?php 
                                                $containsGreatWallAfrica = false;
                                                foreach ($bundle['items'] as $item) {
                                                    if (stripos($item['name'], 'Great Wall Africa') !== false) {
                                                        $containsGreatWallAfrica = true;
                                                        break;
                                                    }
                                                }
                                                if ($containsGreatWallAfrica) {
                                                    continue;
                                                }
                                            ?>
                                            <option 
                                                value="<?php echo htmlspecialchars(json_encode($bundle['items'])); ?>"
                                                data-price="<?php echo $bundle['price']; ?>"
                                                data-bundle='<?php echo json_encode($bundle); ?>'>
                                                <?php echo $bundle['name']; ?> - ₦<?php echo number_format($bundle['price'], 2); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div id="bundle_details" class="card mb-3" style="display: none;">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Bundle Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul id="bundle_items" class="list-group list-group-flush"></ul>
                                        <div class="text-end mt-3 fw-bold">
                                            Total: ₦<span id="bundle_total">0.00</span>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle"></i> No bundles available at this time.
                                </div>
                            <?php endif; ?>

                           <hr>
            <div class="mt-3">
                <div class="row">
                    <div class="col-lg-3">Cost Price: &#8358;<span id="price">0</span></div>
                    <div class="col-lg-2">Bonus: <span><?php echo isset($bonusMapping[$biller_id]) ? $bonusMapping[$biller_id] . '%' : '0%'; ?></span></div>
                    <div class="col-lg-4">Discount Received: &#8358;<span id="profit">0</span></div>
                    <div class="col-lg-3">Amount to Pay: &#8358;<span id="discounted_price">0</span></div>
                </div>
            </div>


            <hr>
                
                    <button type="button" class="btn btn-primary mt-3 w-100" id="confirmButton1">
                        Submit
                    </button>
              
            </form>
                    <!-- Confirmation Modal -->
                    <div class="modal fade" id="confirmationModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">Confirm Subscription</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <h6>Smartcard Number:</h6>
                                        <p class="fw-bold" id="modal_smartcard"></p>
                                    </div>
                                    <div class="mb-3">
                                        <h6>Selected Bundle:</h6>
                                        <p class="fw-bold" id="modal_bundle"></p>
                                        <div id="modal_bundle_items" class="mt-2"></div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div>Original Price:</div>
                                            <div class="fw-bold">₦<span id="modal_original_price">0.00</span></div>
                                        </div>
                                        <div class="col-md-6">
                                            <div>Discount:</div>
                                            <div class="fw-bold text-danger">-₦<span id="modal_discount">0.00</span></div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="text-end fw-bold fs-5">
                                        Total: ₦<span id="modal_total">0.00</span>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" id="modal_confirm_btn" class="btn btn-primary" onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Processing...'; document.getElementById('subscriptionForm').submit();">
                                      <i class="fas fa-check"></i> Confirm Payment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-times-circle"></i> Provider not found
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const bundleSelect = document.getElementById('bundle_select');
        const bouquetCodeInput = document.getElementById('bouquet_code');
        const selectedPriceInput = document.getElementById('selected_price'); // Reference to selected_price input
        const bundleDetails = document.getElementById('bundle_details');
        const bundleItems = document.getElementById('bundle_items');
        const bundleTotal = document.getElementById('bundle_total');
        const costPrice = document.getElementById('price');
        const discountAmount = document.getElementById('profit');
        const amountPayable = document.getElementById('discounted_price');
        const hiddenDiscountedPrice = document.getElementById('hidden_discounted_price');
        const confirmBtn = document.getElementById('confirmButton');
        const modal = new bootstrap.Modal(document.getElementById('confirmationModal')); // Initialize modal

        // Update bundle details when selection changes
        bundleSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (!selectedOption.value) {
                bundleDetails.style.display = 'none';
                return;
            }

            const bundle = JSON.parse(selectedOption.getAttribute('data-bundle'));
            const price = parseFloat(bundle.price); // Ensure price is a number
            const bonus = <?php echo $bonusMapping[$biller_id] ?? 0; ?>;
            const discount = (price * bonus) / 100;
            const payable = price - discount;

            // Update hidden inputs
            bouquetCodeInput.value = bundle.code; // Set bouquet_code to the main bundle code
            selectedPriceInput.value = price.toFixed(2); // Set selected_price to the total bundle price
            hiddenDiscountedPrice.value = payable.toFixed(2);

            // Update bundle details
            bundleItems.innerHTML = '';
            bundle.items.forEach(item => {
                const itemPrice = parseFloat(item.price) || 0; // Ensure item.price is a number
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.innerHTML = `
                    <span>${item.type === 'main' ? '<i class="fas fa-tv text-primary me-2"></i>' : '<i class="fas fa-plus-circle text-success me-2"></i>'} 
                    ${item.name}</span>
                    <span class="badge bg-primary rounded-pill">₦${itemPrice.toFixed(2)}</span>
                `;
                bundleItems.appendChild(li);
            });

            bundleTotal.textContent = price.toFixed(2);
            bundleDetails.style.display = 'block';

            // Update pricing
            costPrice.textContent = price.toFixed(2);
            discountAmount.textContent = discount.toFixed(2);
            amountPayable.textContent = payable.toFixed(2);
        });

        // Confirm button click handler
        confirmBtn.addEventListener('click', function() {
            const smartcard = document.getElementById('smartcard_number').value.trim();
            const selectedOption = bundleSelect.options[bundleSelect.selectedIndex];
            
            if (!smartcard) {
                alert('Please enter your smartcard number');
                return;
            }
            
            if (!selectedOption.value) {
                alert('Please select a bundle');
                return;
            }

            const bundle = JSON.parse(selectedOption.getAttribute('data-bundle'));
            const price = parseFloat(selectedOption.getAttribute('data-price'));
            const bonus = <?php echo $bonusMapping[$biller_id] ?? 0; ?>;
            const discount = (price * bonus) / 100;
            const payable = price - discount;

            // Update modal content
            document.getElementById('modal_smartcard').textContent = smartcard;
            document.getElementById('modal_bundle').textContent = bundle.name;
            document.getElementById('modal_original_price').textContent = price.toFixed(2);
            document.getElementById('modal_discount').textContent = discount.toFixed(2);
            document.getElementById('modal_total').textContent = payable.toFixed(2);

            // Update bundle items in modal
            const modalItems = document.getElementById('modal_bundle_items');
            modalItems.innerHTML = '';
            
            const ul = document.createElement('ul');
            ul.className = 'list-group';
            
            bundle.items.forEach(item => {
                const itemPrice = parseFloat(item.price) || 0; // Ensure item.price is a number
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.innerHTML = `
                    <span>${item.type === 'main' ? '<i class="fas fa-tv text-primary me-2"></i>' : '<i class="fas fa-plus-circle text-success me-2"></i>'} 
                    ${item.name}</span>
                    <span class="badge bg-primary rounded-pill">₦${itemPrice.toFixed(2)}</span>
                `;
                ul.appendChild(li);
            });
              
            modalItems.appendChild(ul);

            // Show modal
            modal.show();
        });

        // Modal confirm button
        document.getElementById('modal_confirm_btn').addEventListener('click', function() {
            document.getElementById('subscriptionForm').submit();
        });
    });
    </script>
</body>
</html>