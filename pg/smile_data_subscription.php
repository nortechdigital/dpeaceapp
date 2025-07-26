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
      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-primary alert-dismissible fade show" role="alert">
          <?= $_SESSION['error'] ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>
        <button onclick="history.back()" class="btn btn-primary mb-3">Back</button>
        <?php if ($providerDetails): ?>
            <div class="card shadow p-4">
                <img src="<?php echo $providerDetails['logo']; ?>" alt="<?php echo $providerDetails['name']; ?> Logo" class="img-fluid mb-3 d-block mx-auto" width="80px" height="auto">
                <h2 class="text-center bg-primary text-light h5"><?php echo $providerDetails['name']; ?> SUBSCRIPTION</h2>
                <form id="purchaseForm" action="./_/ac_process_smile.php" method="post">
        			<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <fieldset>
                        <div class="form-group mt-3">
                            <label for="actype" class="form-label mb-2"><strong>Account Type</strong></label>  
                            <select id="actype" name="actype" class="form-control mb-2" required>
                                <option value="" disabled selected>Select Account Type</option> 
                                <option value='PhoneNumber'>Phone Number</option>
                                <!-- <option value='AccountNumber'>Account Number</option> -->
                            </select>
                            <div class="invalid-feedback">Please select an account type</div>
                        </div>
                        
                        <div class="form-group mt-3" id="phoneNumberDiv" style="display:none;">
                            <label for="phone" class="mb-2"><strong>Phone Number</strong></label>
                            <div class="input-group">
                                <input type="text" name="phone" id="phone" class="form-control" 
                                       placeholder="Enter Phone Number" 
                                       pattern="234[0-9]{10}" 
                                       title="Phone number must start with 234 and be 13 digits total"
                                       required
                                       oninput="formatPhoneNumber(this)" 
                                       onfocus="this.setSelectionRange(3,3)" />
                            </div>
                            <div class="invalid-feedback">Please enter a valid phone number starting with 234</div>
                        </div>

                        <div class="form-group mt-3" id="accountNumberDiv" style="display:none;">
                            <label for="account_value" class="mb-2">Account Number (10 digit)</label>
                            <input type="text" name="account_value" id="account_value" class="form-control" 
                                   placeholder="Enter Account Number" 
                                   pattern="[0-9]{10}"
                                   title="Account number must be 10 digits"/>
                            <div class="invalid-feedback">Please enter a valid 10-digit account number</div>
                        </div>

                        <div class="form-group mt-3">
                            <label for="bundleTypeCode" class="mb-2"><strong>Smile Plan</strong></label>
                            <select name="bundleTypeCode" id="bundleTypeCode" class="form-control" required>
                                <option value="" disabled selected>Select Smile Plan</option>
                                <?php foreach ($bundle_code as $row): ?>
                                    <option value="<?php echo $row['bundle_code']; ?>" data-price="<?php echo $row['price']; ?>">
                                        <?php echo $row['plan_name']; ?> - &#8358;<?php echo number_format($row['price'], 2); ?> - (Validity: <?php echo $row['validity']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a plan</div>
                        </div>

                        <hr>
                        <div class="mt-3">
                            <div class="row">
                                <div class="col-lg-3">Bundle Price: &#8358;<span name="price" id="price">0</span></div>
                                <div class="col-lg-2">Bonus: 5%</div>
                                <div class="col-lg-4">Discount Received: &#8358;<span name="profit" id="profit">0</span></div>
                                <div class="col-lg-3">Amount to Pay: &#8358;<span name="discounted_price" id="discounted_price">0</span></div>
                                <input type="hidden" name="discounted_price" id="hidden_discounted_price" value="0">
                                <input type="hidden" name="hidden_amount" id="hidden_amount" value="0">
                            </div>
                        </div>
                        <hr>
                        <input type="hidden" id="plan" name="plan" value="">
                        <button type="button" class="btn btn-primary w-100 btn-block mt-3" id="submitButton">Purchase Bundle</button>
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
                <button type="button" class="btn btn-primary" id="confirmPurchase" onclick="this.disabled=true; this.innerHTML='Processing...'; document.getElementById('purchaseForm').submit();">Ok</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide fields based on account type
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

    // Price calculation
    document.getElementById('bundleTypeCode').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const discount = price * 0.05;
        const discountedPrice = price - discount;

        document.getElementById('price').textContent = price.toFixed(2);
        document.getElementById('profit').textContent = discount.toFixed(2);
        document.getElementById('discounted_price').textContent = discountedPrice.toFixed(2);
        document.getElementById('hidden_discounted_price').value = discountedPrice.toFixed(2);
        document.getElementById('hidden_amount').value = price.toFixed(2);
    });

    // Form validation and modal population
    document.getElementById('submitButton').addEventListener('click', function () {
        const accountType = document.getElementById('actype').value;
        const phone = document.getElementById('phone').value;
        const bundleTypeCode = document.getElementById('bundleTypeCode').value;

        if (!accountType || (!phone && accountType === 'PhoneNumber') || !bundleTypeCode) {
            alert('Please fill in all required fields.');
            return false; // Prevent modal from loading
        }

        // Populate modal values
        const selectedOption = document.getElementById('bundleTypeCode').options[document.getElementById('bundleTypeCode').selectedIndex];
        const planName = selectedOption.textContent.trim();
        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const discount = price * 0.05;
        const discountedPrice = price - discount;

        document.getElementById('modal_plan_name').textContent = planName;
    	document.getElementById('plan').value = planName;
        document.getElementById('modal_phone_number').textContent = phone || 'N/A';
        document.getElementById('modal_price').textContent = price.toFixed(2);
        document.getElementById('modal_discount').textContent = discount.toFixed(2);
        document.getElementById('modal_discounted_price').textContent = discountedPrice.toFixed(2);

        // Show confirmation modal
        const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        confirmationModal.show();
    });
});

function formatPhoneNumber(input) {
    // Ensure the input starts with '234' and contains exactly 13 digits
    input.value = input.value.startsWith('234') 
        ? input.value.slice(0, 13) 
        : '234' + input.value.replace(/^0+/, '').slice(0, 10);

    // Validate and show feedback
    if (/^234[0-9]{10}$/.test(input.value)) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    } else {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
    }
}
</script>