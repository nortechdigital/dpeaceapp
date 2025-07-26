<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Fetch data plans for the selected provider
$query = "SELECT * FROM swift_data_plans";
$stmt = $conn->prepare($query);
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
                <form action="./_/ac_process_swift.php" method="post" id="dataForm">
                    <div class="mb-3">
                        <label for="">Customer ID</label>            
                        <input type="text" name="cust_reference" id="cust_reference" class="form-control mt-3" placeholder="Enter Customer ID" required>
                    </div>
                    <div class="mb-3">
                        <label for="">Select Data Plan</label> 
                        <select class="form-control" name="" id="">
                            <option value="">Select Data Plan</option>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>" data-price="<?php echo $row['price']; ?>"><?php echo $row['plan_name']; ?> - &#8358;<?php echo number_format($row['price'], 2); ?> (Validity: <?php echo $row['validity']; ?> days)</option>
                        <?php endwhile; ?>
                        </select>           
                        <input type="hidden" name="hidden_amount" id="hidden_amount" value="0">
                        <script>
                            document.querySelector('select[name]').addEventListener('change', function() {
                                const selectedOption = this.options[this.selectedIndex];
                                const price = selectedOption.getAttribute('data-price');
                                document.getElementById('hidden_amount').value = price || 0;
                            });
                        </script>
                    </div>
                    <hr>
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-lg-3">Cost Price: &#8358;<span name="price" id="price">0</span></div>
                            <div class="col-lg-2">Bonus: <span>10%</span></div>
                            <div class="col-lg-4">Discount Received: &#8358;<span name="profit" id="profit">0</span></div>
                            <div class="col-lg-3">Amount to Pay: &#8358;<span name="discounted_price" id="discounted_price">0</span></div>
                        </div>
                        <input type="hidden" name="discounted_price" id="hidden_discounted_price" value="0">
                    </div>
                    <script>
                        document.querySelector('select[name]').addEventListener('change', function() {
                            const selectedOption = this.options[this.selectedIndex];
                            const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                            const bonusPercentage = 10; // 7% bonus
                            const profit = (price * bonusPercentage) / 100;
                            const discountedPrice = price - profit;

                            document.getElementById('price').textContent = price.toFixed(2);
                            document.getElementById('profit').textContent = profit.toFixed(2);
                            document.getElementById('discounted_price').textContent = discountedPrice.toFixed(2);
                            document.getElementById('hidden_discounted_price').value = discountedPrice.toFixed(2);
                            document.getElementById('hidden_amount').value = price.toFixed(2);
                        });
                    </script>
                    
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
                                <p>Customer Reference: <span id="modalPhoneNumber"></span></p>
                                <p>Data Plan: <span id="modalDataPlan"></span></p>
                                <p>Bundle Price: &#8358;<span id="modalPrice">0</span></p>
                                <p>Discount Received: &#8358;<span id="modalDiscountReceived">0</span></p>
                                <script>
                                    
                                </script>
                                <p>Amount to Pay: &#8358;<span id="modalAmountToPay"></span></p>
                                
                                <p class="text-danger">Are you sure you want to proceed with this transaction?</p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-primary" id="modalSubmitButton">OK</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#confirmationModal').modal('hide');">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    
                </script>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">Invalid provider selected.</div>
        <?php endif; ?>
    </div>
  </div>
</div>

<script>
    document.getElementById('confirmButton').addEventListener('click', function () {
        const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        confirmationModal.show();
    });

    document.getElementById('modalSubmitButton').addEventListener('click', function () {
        document.getElementById('dataForm').submit();
    });
        document.getElementById('confirmButton').addEventListener('click', function () {
        const phoneNumber = document.getElementById('cust_reference').value;
        const selectedOption = document.querySelector('select[name]').selectedOptions[0];
        const dataPlan = selectedOption.textContent.trim();
        const price = selectedOption.getAttribute('data-price');
        const amountToPay = document.getElementById('hidden_discounted_price').value;

        document.getElementById('modalPhoneNumber').textContent = phoneNumber;
        document.getElementById('modalDataPlan').textContent = dataPlan;
        document.getElementById('modalPrice').textContent = parseFloat(price).toFixed(2);
        document.getElementById('modalAmountToPay').textContent = parseFloat(amountToPay).toFixed(2);
    });
    document.getElementById('confirmButton').addEventListener('click', function () {
        const price = parseFloat(document.getElementById('hidden_amount').value) || 0;
        const discountedPrice = parseFloat(document.getElementById('hidden_discounted_price').value) || 0;
        const discountReceived = price - discountedPrice;

        document.getElementById('modalDiscountReceived').textContent = discountReceived.toFixed(2);
    });
</script>