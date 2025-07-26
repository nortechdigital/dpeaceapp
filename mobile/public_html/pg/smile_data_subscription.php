<?php
// session_start();
include_once './_/conn.php';
// include_once './_/ac_config.php';
// include_once './_/functions.php'; // Include the functions file

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
        'description' => '9mobile data subscription plans.'
    ],
    'airtel-data' => [
        'name' => 'AIRTEL',
        'logo' => 'img/logo/airtel_logo.png',
        'description' => 'Airtel data subscription plans.'
    ],
    'glo-data' => [
        'name' => 'Glo',
        'logo' => 'img/logo/glo_logo.jpg',
        'description' => 'Glo data subscription plans.'
    ],
    'mtn-data' => [
        'name' => 'MTN',
        'logo' => 'img/logo/mtn_logo.png',
        'description' => 'MTN data subscription plans.'
    ],
    'smile' => [
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

// Fetch Smile plans from the database
$sql = "SELECT * FROM smile_plan";
$result = $conn->query($sql);
$bundle_code = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bundle_code[] = $row;
    }
} else {
    echo "<div class='alert alert-warning'>No data plans found.</div>";
}
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
                <form id="purchaseForm" action="./_/ac_process_smile.php" method="post">
                    <fieldset>
                        <div class="form-group mt-3">
                            <label for="actype" class="form-label mb-2"><strong>Account Type</strong></label>  
                            <select id="actype" name="actype" class="form-control mb-2" required>
                                <option value="" disabled="" selected="">Select Account Type</option> 
                                <option value='PhoneNumber'>Phone Number</option>
                                <!-- <option value='AccountNumber'>Account Number</option> -->
                            </select>
                        </div>
                        
                        <div class="form-group mt-3" id="phoneNumberDiv" style="display:none;">
    <label for="phone" class="mb-2"><strong>Phone Number</strong></label>
    <div class="input-group">
        
        <input type="text" name="phone" id="phone" class="form-control" 
               placeholder="Enter Phone Number" 
               oninput="formatPhoneNumber(this)" 
               onfocus="this.setSelectionRange(3,3)" />
    </div>
</div>

                        <div class="form-group mt-3" id="accountNumberDiv" style="display:none;">
                            <label for="account_value" class="mb-2">Account Number (10 digit)</label>
                            <input type="text" name="account_value" id="account_value" class="form-control" placeholder="Enter Account Number" />
                        </div>

                        <script>
                            document.getElementById('actype').addEventListener('change', function() {
                                var phoneNumberDiv = document.getElementById('phoneNumberDiv');
                                var accountNumberDiv = document.getElementById('accountNumberDiv');
                                if (this.value === 'PhoneNumber') {
                                    phoneNumberDiv.style.display = 'block';
                                    accountNumberDiv.style.display = 'none';
                                } else if (this.value === 'AccountNumber') {
                                    phoneNumberDiv.style.display = 'none';
                                    accountNumberDiv.style.display = 'block';
                                } else {
                                    phoneNumberDiv.style.display = 'none';
                                    accountNumberDiv.style.display = 'none';
                                }
                            });
                        </script>

                        <div class="form-group mt-3">
                            <label for="bundleTypeCode" class="mb-2"><strong>Smile Plan</strong></label>
                            <select name="bundleTypeCode" id="bundleTypeCode" class="form-control">
                                <option value="">Select Smile Plan</option>
                                <?php foreach ($bundle_code as $row): ?>
                                    <option value="<?php echo $row['bundle_code']; ?>" data-price="<?php echo $row['price']; ?>">
                                        <?php echo $row['plan_name']; ?> - &#8358;<?php echo number_format($row['price'], 2); ?> - (Validity: <?php echo $row['validity']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <hr>
                        <div class="mt-3">
                            <div class="row">
                                <div class="col-lg-3">Bundle Price: &#8358;<span name="price" id="price">0</span></div>
                                <div class="col-lg-2">Bonus: 5%</div>
                                <div class="col-lg-4">Discount Received: &#8358;<span name="profit" id="profit">0</span></div>
                                <div class="col-lg-3">Amount to Pay: &#8358;<span name="discounted_price" id="discounted_price">0</span></div>
                                <input type="hidden" name="discounted_price" id="hidden_discounted_price" value="0">
                                <input type="hidden" name="hidden_amount" id="hidden_amount" value="0" readonly>
                                <script>
                                    document.getElementById('bundleTypeCode').addEventListener('change', function() {
                                        const selectedOption = this.options[this.selectedIndex];
                                        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;

                                        document.getElementById('hidden_amount').value = price.toFixed(2);
                                    });
                                </script>
                                <script>
                                    document.getElementById('bundleTypeCode').addEventListener('change', function() {
                                        const selectedOption = this.options[this.selectedIndex];
                                        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                                        const discount = price * 0.05;
                                        const discountedPrice = price - discount;

                                        document.getElementById('hidden_discounted_price').value = discountedPrice.toFixed(2);
                                    });
                                </script>
                            </div>
                        </div>
                        <hr>
                        <button type="button" class="btn btn-primary w-100 btn-block mt-3" data-bs-toggle="modal" data-bs-target="#confirmationModal">Purchase Bundle</button>
                    </fieldset>
                </form> 
            </div>
        <?php else: ?>
            <div class="alert alert-danger">Invalid provider selected.</div>
        <?php endif; ?>
    </div>
  </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-center w-100" id="confirmationModalLabel">Confirm Purchase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Plan Name:</strong> <span id="modal_plan_name">N/A</span></p>
                <p><strong>Phone Number:</strong> <span id="modal_phone_number">N/A</span></p>
                <p><strong>Bundle Price:</strong> &#8358;<span id="modal_price">0</span></p>
                <p><strong>Discount Received:</strong> &#8358;<span id="modal_discount">0</span></p>
                <p><strong>Amount to Pay:</strong> &#8358;<span id="modal_discounted_price">0</span></p>
                <p class="text-danger">Are you sure you want to purchase this bundle?</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" id="confirmPurchase">Ok</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
        document.getElementById('bundleTypeCode').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const planName = selectedOption.textContent.trim();
                const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                const discount = price * 0.05;
                const discountedPrice = price - discount;
                const profit = discount;

                const phoneNumber = document.getElementById('phone').value || 'N/A';

                document.getElementById('price').innerText = price.toFixed(2);
                document.getElementById('discounted_price').innerText = discountedPrice.toFixed(2);
                document.getElementById('profit').innerText = profit.toFixed(2);

                document.getElementById('modal_plan_name').innerText = planName;
                document.getElementById('modal_phone_number').innerText = phoneNumber;
                document.getElementById('modal_price').innerText = price.toFixed(2);
                document.getElementById('modal_discount').innerText = discount.toFixed(2);
                document.getElementById('modal_discounted_price').innerText = discountedPrice.toFixed(2);
        });
</script>

<script>
    document.getElementById('bundleTypeCode').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const planName = selectedOption.textContent.trim();
        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const discount = price * 0.05;
        const discountedPrice = price - discount;
        const profit = discount;

        document.getElementById('price').innerText = price.toFixed(2);
        document.getElementById('discounted_price').innerText = discountedPrice.toFixed(2);
        document.getElementById('profit').innerText = profit.toFixed(2);

        document.getElementById('modal_plan_name').innerText = planName;
        document.getElementById('modal_price').innerText = price.toFixed(2);
        document.getElementById('modal_discount').innerText = discount.toFixed(2);
        document.getElementById('modal_discounted_price').innerText = discountedPrice.toFixed(2);
    });

    document.getElementById('confirmPurchase').addEventListener('click', function() {
        document.getElementById('purchaseForm').submit();
    });
</script>
<script>
function formatPhoneNumber(input) {
    // Apply your original formatting logic
    input.value = input.value.startsWith('234') ? input.value : '234' + input.value.replace(/^0+/, '');
    
    // Ensure cursor stays after the prefix
    if (input.value.length <= 3) {
        input.setSelectionRange(3, 3);
    }
}

document.getElementById('phone').addEventListener('keydown', function(e) {
    // Prevent backspace/delete from removing the prefix
    if ((e.key === 'Backspace' || e.key === 'Delete') && 
        (this.selectionStart <= 3 || this.selectionEnd <= 3)) {
        e.preventDefault();
        this.setSelectionRange(3, 3);
    }
});
</script>