<?php
include_once './_/conn.php';
include_once './_/ac_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

$biller_id = $_GET['biller_id'] ?? null;
$phone_number = $_GET['phone_number'] ?? null;

if (!$biller_id) {
    echo "<div class='alert alert-danger'>Biller ID is required.</div>";
    exit;
}

// Fetch available data plans from VAS2Nets API
$endpoint = VAS2NETS_BASE_URL . VAS2NETS_ENV_URI . "bouquet/vtu/" . $biller_id;

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . base64_encode(VAS2NETS_USERNAME . ':' . VAS2NETS_PASSWORD)
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

$data_plans = [];
if ($httpCode == 200) {
    $responseData = json_decode($response, true);

    if (isset($responseData['bouquets'])) {
        $data_plans = $responseData['bouquets'];
    } elseif (isset($responseData['data']['bouquets'])) {
        $data_plans = $responseData['data']['bouquets'];
    } elseif (isset($responseData['data'])) {
        $data_plans = $responseData['data'];
    }
}  

// Fallback data plans if API fails
if (empty($data_plans)) {
    echo "<!-- Using fallback data plans -->\n";
    $data_plans = [
        ['code' => 'PLAN-1GB', 'name' => '1GB Data Plan', 'price' => 500],
        ['code' => 'PLAN-2GB', 'name' => '2GB Data Plan', 'price' => 1000],
    ];
}
// Add logo based on biller_id
$logo = ''; $bonus = 0;
if ($biller_id === 'MTN-DATA') {
    $logo = './img/logo/mtn_logo.png';
    $name = 'MTN';
    $bonus = 5;
} elseif ($biller_id === 'Airtel-DATA') {
    $logo = './img/logo/airtel_logo.png';
    $name = 'Airtel';
    $bonus = 5;
} elseif ($biller_id === 'GLO-DATA') {
    $logo = './img/logo/glo_logo.jpg';
    $name = 'Glo';
    $bonus = 5;
} elseif ($biller_id === '9Mobile-DATA') {
    $logo = './img/logo/9moble_logo.png';
    $name = '9mobile';
    $bonus = 5;
}

?>

<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php"; ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
        <button onclick="history.back()" class="btn btn-primary mb-3">Back</button>
        <div class="card shadow p-4">
            <div class="text-center mb-3">
                <img src="<?php echo htmlspecialchars($logo); ?>" alt="<?php echo htmlspecialchars($biller_id); ?>" class="img-fluid mx-2" width="80px" height="auto">
            </div>
            <h2 class="text-center bg-primary text-light h5">Data Subscription</h2>
            <form id="dataPlanForm" action="./_/ac_process_data_vas2nets.php" method="post">
                <input type="hidden" name="biller_id" value="<?php echo htmlspecialchars($biller_id); ?>">
                <input type="text" name="phone_number" id="phone_number" class="form-control mt-3" 
                       value="<?php echo htmlspecialchars($phone_number); ?>" placeholder="Enter Phone Number" required>
                
                <div class="form-group mt-3">
                    <label for="bouquet_code">Select Data Plan</label>
                    <select name="bouquet_code" id="bouquet_code" class="form-control" required>
                        <option value="">-- Select Data Plan --</option>
                        <?php foreach ($data_plans as $plan): ?>
                            <?php if (is_array($plan)): ?>
                                <option value="<?php echo htmlspecialchars($plan['code'] ?? $plan['id']); ?>" data-price="<?php echo htmlspecialchars($plan['price'] ?? $plan['amount']); ?>">
                                    <?php echo htmlspecialchars($plan['name'] ?? $plan['description']); ?> 
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                        
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
                        <div class="col-lg-3">Bundle Price: &#8358;<span name="price" id="price">0</span></div>
                        <input type="hidden" name="hidden_price" id="hidden_price" class="form-control col-3" readonly value="0">
                        <div class="col-lg-2">Bonus: <?= $bonus ?>%</div>
                        <input type="hidden" name="bonus" id="bonus" value="<?= $bonus ?>">
                        <div class="col-lg-4">Discount Received: &#8358;<span name="profit" id="profit">0</span></div>
                        <div class="col-lg-3">Amount to Pay: &#8358;<span name="discounted_price" id="discounted_price">0</span></div>
                        <input type="hidden" name="discounted_price" id="hidden_discounted_price" value="0">
                    </div>
                </div>
                <hr>

                <div class="form-group mt-3 text-center">
                    <button type="button" class="btn btn-primary w-100 mt-3" id="confirmButton">Purchase Bundle</button>
                </div>
            </form>
        </div>
    </div>
  </div>
</div>

<!-- Confirmation Modal -->
<div class="modal" id="confirmationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header justify-content-center">
                <h5 class="modal-title">Confirm Transaction</h5>
            </div>
            <div class="modal-body justify-content-between">
                <p>Phone Number: <span id="modalPhoneNumber"></span></p>
                <p>Data Plan: <span id="modalDataPlan"></span></p>
                <p>Bundle Price: &#8358;<span id="modalPrice">0</span></p>
                <p>Discount Received: &#8358;<span id="modalDiscountReceived">0</span></p>
                <p>Amount to Pay: &#8358;<span id="modalAmountToPay"></span></p>
                <!-- <p>Note: You will receive a 2% bonus on this transaction.</p> -->
                <p class="text-danger">Are you sure you want to proceed with this transaction?</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" id="confirmTransaction">OK</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#confirmationModal').modal('hide');">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('bouquet_code').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = parseFloat(selectedOption.getAttribute('data-price'));
        let bonus = document.getElementById('bonus').value / 100;
        const discount = price * bonus;
        const discountedPrice = price - discount;
        const profit = discount;

        document.getElementById('price').innerText = price.toFixed(2);
        document.getElementById('hidden_price').value = price.toFixed(2);
        document.getElementById('discounted_price').innerText = discountedPrice.toFixed(2);
        document.getElementById('profit').innerText = profit.toFixed(2);
        document.getElementById('hidden_discounted_price').value = discountedPrice.toFixed(2);
        document.getElementById('modalDiscountReceived').innerText = profit.toFixed(2); // Update discount in modal
    });

    document.getElementById('confirmButton').addEventListener('click', function() {
        const phoneNumber = document.getElementById('phone_number').value;
        const selectedPlan = document.getElementById('bouquet_code');
        const planName = selectedPlan.options[selectedPlan.selectedIndex].text;
        const amountToPay = document.getElementById('hidden_discounted_price').value;
        const discountReceived = document.getElementById('profit').innerText;

        document.getElementById('modalPrice').innerText = document.getElementById('hidden_price').value;
        document.getElementById('modalPhoneNumber').innerText = phoneNumber;
        document.getElementById('modalDataPlan').innerText = planName;
        document.getElementById('modalAmountToPay').innerText = amountToPay;
        document.getElementById('modalDiscountReceived').innerText = discountReceived; // Display discount received

        $('#confirmationModal').modal('show');
    });

    document.getElementById('confirmTransaction').addEventListener('click', function() {
        document.getElementById('dataPlanForm').submit();
    });
</script>


