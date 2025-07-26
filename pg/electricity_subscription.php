<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

// Define providers array
$providers = [
    'aba' => [
        'id' => 'ABA',
        'name' => 'Aba Electricity',
        'logo' => 'img/logo/aba_logo.png',
        'bonus' => '1.40%',
        'description' => 'Aba Electricity subscription plans.'
    ],
    'abuja' => [
        'id' => 'AEDC',
        'name' => 'Abuja Electricity',
        'logo' => 'img/logo/aedc.png',
        'bonus' => '1.50%',
        'description' => 'Abuja Electricity subscription plans.'
    ],
    'benin' => [
        'id' => 'BEDC',
        'name' => 'Benin Electricity',
        'logo' => 'img/logo/benin_logo.jpeg',
        'bonus' => '1.00%',
        'description' => 'Benin Electricity subscription plans.'
    ],
    'eko' => [
        'id' => 'EKEDC',
        'name' => 'Eko Electricity',
        'logo' => 'img/logo/ekedc.png',
        'bonus' => '1.50%',
        'description' => 'Eko Electricity subscription plans.'
    ],
    'enugu' => [
        'id' => 'EEDC',
        'name' => 'Enugu Electricity',
        'logo' => 'img/logo/enugu_logo.png',
        'bonus' => '1.30%',
        'description' => 'Enugu Electricity subscription plans.'
    ],
    'ibadan' => [
        'id' => 'IBEDC',
        'name' => 'Ibadan Electricity',
        'logo' => 'img/logo/ibedc.png',
        'bonus' => '0.60%',
        'description' => 'Ibadan Electricity subscription plans.'
    ],
    'ikeja' => [
        'id' => 'IKEDC',
        'name' => 'Ikeja Electricity',
        'logo' => 'img/logo/ikeja.png',
        'bonus' => '1.50%',
        'description' => 'Ikeja Electricity subscription plans.'
    ],
    'jos' => [
        'id' => 'JEDC',
        'name' => 'Jos Electricity',
        'logo' => 'img/logo/jos.png',
        'bonus' => '0.60%',
        'description' => 'Jos Electricity subscription plans.'
    ],
    'kaduna' => [
        'id' => 'KAEDC',
        'name' => 'Kaduna Electricity',
        'logo' => 'img/logo/kaduna.png',
        'bonus' => '1.30%',
        'description' => 'Kaduna Electricity subscription plans.'
    ],
    'kano' => [
        'id' => 'KEDCO',
        'name' => 'Kano Electricity',
        'logo' => 'img/logo/kedco.png',
        'bonus' => '1.00%',
        'description' => 'Kano Electricity subscription plans.'
    ],
    'port_harcourt' => [
        'id' => 'PHEDC',
        'name' => 'Port Harcourt Electricity',
        'logo' => 'img/logo/phedc.png',
        'bonus' => '1.50%',
        'description' => 'Port Harcourt Electricity subscription plans.'
    ],
    'yola' => [
        'id' => 'YEDC',
        'name' => 'Yola Electricity',
        'logo' => 'img/logo/yola_logo.jpeg',
        'bonus' => '0.50%',
        'description' => 'Yola Electricity subscription plans.'
    ],
    // Add other providers here...
];

// Bonus percentage mapping
$bonusMapping = [
    'ABA' => 0.014,
    'AEDC' => 0.015,
    'BEDC' => 0.01,
    'EKEDC' => 0.015,
    'EEDC' => 0.013,
    'IBEDC' => 0.006,
    'IKEDC' => 0.015,
    'JEDC' => 0.006,
    'KAEDC' => 0.013,
    'KEDCO' => 0.01,
    'PHEDC' => 0.015,
    'YEDC' => 0.005,
];

// Get provider details
$provider = isset($_GET['provider']) ? $_GET['provider'] : null;
$providerDetails = isset($providers[$provider]) ? $providers[$provider] : null;
if ($providerDetails) {
    $providerDetails['id'];
} else {
    echo "Provider ID not found.";
}
$providerName = $providerDetails['name'];

?>


<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php" ?>
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
        <button onclick="history.back()" class="btn btn-secondary mb-3">Back</button>
        <?php if ($providerDetails): ?>
            <div class="card shadow p-4">
            <img src="<?php echo $providerDetails['logo']; ?>" alt="<?php echo $providerDetails['name']; ?> Logo" class="img-fluid mb-3 d-block mx-auto" width="80px" height="auto">
            <h2 class="text-center bg-primary text-light h5"><?php echo $providerDetails['name']; ?> Subscription</h2>
            <form action="./_/ac_process_electricity.php" method="post" id="electricityForm">
        		<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="biller_id" id="biller_id" value="">
        		<input type="hidden" name="providerName" id="providerName" value="<?php echo $providerName ?>">
                
                <select name="account_type" id="account_type" class="form-control" required>
                    <option value="">Select Account Type</option>
                    <option value="prepaid">Prepaid Meter</option>
                    <option value="postpaid">Post Paid</option>
                </select>
                <input type="number" name="account_value" id="account_value" class="form-control mt-3" placeholder="Enter Meter Number or Account Number">
                <input type="number" name="amount" id="amount" class="form-control mt-3" placeholder="Enter Amount" min="1000" required>
                <input type="tel" name="phone" id="phone" class="form-control mt-3" placeholder="Enter Phone Number" required pattern="^[0-9]{11}$" title="Please enter a valid phone number (11 digits)" inputmode="numeric" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);">
                <hr>
                <div class="mt-3">
                    <div class="row">
                        <div class="col-lg-3">Cost Price: &#8358;<span name="price" id="price">0</span></div>
                        <div class="col-lg-2">Bonus: <span><?php echo $providerDetails['bonus']; ?></span></div>
                        <div class="col-lg-4">Discount Received: &#8358;<span name="profit" id="profit">0</span></div>
                        <div class="col-lg-3">Amount to Pay: &#8358;<span name="discounted_price" id="discounted_price">0</span></div>
                    </div>
                    <input type="hidden" name="discounted_price" id="hidden_discounted_price" value="0">
                </div>
                <script>
                    document.getElementById('amount').addEventListener('input', function () {
                        const amount = parseFloat(this.value) || 0;
                        const providerId = "<?php echo $providerDetails['id']; ?>";
                        const bonusPercentage = <?php echo json_encode($bonusMapping); ?>[providerId] || 0;

                        const costPrice = amount;
                        const bonus = amount * bonusPercentage;
                        const discountedPrice = amount - bonus;

                        document.getElementById('price').textContent = costPrice.toFixed(2);
                        document.getElementById('profit').textContent = bonus.toFixed(2);
                        document.getElementById('discounted_price').textContent = discountedPrice.toFixed(2);
                        document.getElementById('hidden_discounted_price').value = discountedPrice.toFixed(2);
                    });

                    const accountTypeElement = document.getElementById('account_type');
                    const billerIdElement = document.getElementById('biller_id');
                    const providerId = "<?php echo $providerDetails['id']; ?>";

                    accountTypeElement.addEventListener('change', function () {
                        const accountType = this.value;
                        billerIdElement.value = providerId + (accountType === 'prepaid' ? 'A' : accountType === 'postpaid' ? 'B' : '');
                    });
                </script>
                <hr>
                
                    <button type="button" class="btn btn-primary mt-3 w-100" id="confirmButton">
                        Submit
                    </button>
               
            </form>

            <!-- Modal -->
            <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-content">
                            <div class="modal-header justify-content-center">
                                <h5 class="modal-title">Confirm Transaction</h5>
                            </div>
                            <div class="modal-body justify-content-between">
                                <p>Account Type: <strong><span id="modalAccountType"></span></strong></p>
                                <p>Meter/Account Number: <strong><span id="modalAccountValue"></span></strong></p>
                                <p>Phone Number: <strong><span id="modalPhoneNumber"></span></strong></p>
                                <p>Cost Price: <strong>&#8358;<span id="modalPrice">0</span></strong></p>
                                <p>Discount Received: <strong>&#8358;<span id="modalDiscount"></span></strong></p>
                                <p>Amount to Pay: <strong>&#8358;<span id="modalAmountToPay"></span></strong></p>
                                <!-- <p>Note: You will receive a 0.3% bonus on this transaction.</p> -->
                                <p class="text-danger">Are you sure you want to proceed with this transaction?</p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-primary" id="modalSubmitButton" onclick="this.disabled = true; this.textContent = 'Processing...'; document.getElementById('electricityForm').submit();">OK</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                        <script>
                            document.getElementById('confirmButton').addEventListener('click', function () {
                                const accountType = document.getElementById('account_type').value;
                                const accountValue = document.getElementById('account_value').value;
                                const amount = parseFloat(document.getElementById('amount').value) || 0;
                                const phone = document.getElementById('phone').value;

                                // Validate account type
                                if (!accountType) {
                                    alert('Please select an account type.');
                                    return;
                                }

                                // Validate account value
                                if (!accountValue || isNaN(accountValue) || accountValue.length < 6) {
                                    alert('Please enter a valid meter/account number (minimum 6 digits).');
                                    return;
                                }

                                // Validate amount
                                if (!amount || amount < 1000) {
                                    alert('Amount must be at least 1000.');
                                    return;
                                }

                                // Validate phone number
                                if (!phone || !/^[0-9]{11}$/.test(phone)) {
                                    alert('Please enter a valid phone number (11 digits).');
                                    return;
                                }

                                // Populate modal fields
                                document.getElementById('modalPhoneNumber').textContent = phone;
                                document.getElementById('modalAccountValue').textContent = accountValue;
                                document.getElementById('modalAccountType').textContent = accountType;
                                document.getElementById('modalPrice').textContent = document.getElementById('price').textContent;
                                document.getElementById('modalDiscount').textContent = document.getElementById('profit').textContent;
                                document.getElementById('modalAmountToPay').textContent = document.getElementById('discounted_price').textContent;

                                // Show confirmation modal
                                const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                                confirmationModal.show();
                            });

                            document.getElementById('modalSubmitButton').addEventListener('click', function () {
                                document.getElementById('electricityForm').submit();
                            });

                            document.querySelector('.btn-secondary[data-bs-dismiss="modal"]').addEventListener('click', function () {
                                const modalElement = document.getElementById('confirmationModal');
                                modalElement.classList.remove('show');
                                document.body.classList.remove('modal-open');
                                document.querySelector('.modal-backdrop').remove();
                            });
                        </script>
                    </div>
                </div>
            </div>
            </div>
        <?php else: ?>
            <p class="text-center">Provider not found.</p>
        <?php endif; ?>
    </div>
  </div>
</div>