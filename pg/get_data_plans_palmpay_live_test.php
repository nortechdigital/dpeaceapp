<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

// Initialize bonus percentage
$bonus = 5; // 5% bonus by default

class PalmPayConfig {
    const TEST_ENV_URL = 'https://open-gw-daily.palmpay-inc.com';
    const PROD_ENV_URL = 'https://open-gw-prod.palmpay-inc.com';
    
    public static $appId = 'L250530142620642487801';
    public static $merchantPrivateKeyFile = './palmpay/private.pem';
    public static $palmPayPublicKeyFile = './palmpay/public.pem';
    public static $countryCode = 'NG';
    public static $isTestEnv = false;
    
    public static function getBaseUrl() {
        return self::$isTestEnv ? self::TEST_ENV_URL : self::PROD_ENV_URL;
    }
}

class PalmPaySignature {
    public static function generateSignature(array $params) {
        // Step 1: Sort and concatenate parameters
        $strA = self::buildSignString($params);
        
        // Step 2: Generate MD5 hash (uppercase)
        $md5Str = strtoupper(md5($strA));
        
        // Step 3: Sign with private key
        return self::rsaSign($md5Str);
    }
    
    private static function buildSignString(array $params) {
        // Remove empty values and trim spaces
        $filteredParams = array_filter($params, function($value) {
            return !empty($value) && trim($value) !== '';
        });
        
        // Sort by key in ASCII order
        ksort($filteredParams);
        
        // Build key=value pairs
        $parts = [];
        foreach ($filteredParams as $key => $value) {
            $parts[] = $key . '=' . trim($value);
        }
        
        return implode('&', $parts);
    }
    
    private static function rsaSign($data) {
        // Read private key from file
        $privateKey = file_get_contents(PalmPayConfig::$merchantPrivateKeyFile);
        if ($privateKey === false) {
            throw new Exception('Failed to read private key file');
        }
        
        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA1);
        return base64_encode($signature);
    }
    
    public static function verifyCallbackSignature($data, $signature) {
        // Read public key from file
        $publicKey = file_get_contents(PalmPayConfig::$palmPayPublicKeyFile);
        if ($publicKey === false) {
            throw new Exception('Failed to read public key file');
        }
        
        $signature = base64_decode($signature);
        return openssl_verify($data, $signature, $publicKey, OPENSSL_ALGO_SHA1) === 1;
    }
}

class PalmPayBillerService {
    /**
     * Query available items for a specific biller and scene code
     * 
     * @param string $sceneCode Business scenario code (e.g., 'airtime', 'data')
     * @param string $billerId Operator ID (e.g., 'NTN', 'WIN')
     * @return array API response containing item list
     * @throws Exception If the API request fails
     */
    public static function queryItems($sceneCode, $billerId) {
        // Prepare request data
        $requestData = [
            'requestTime' => round(microtime(true) * 1000), // Current timestamp in milliseconds
            'nonceStr' => self::generateNonceStr(), // Random string
            'version' => 'V2', // API version
            'sceneCode' => $sceneCode,
            'billerId' => $billerId
        ];
        
        // Generate signature
        $signature = PalmPaySignature::generateSignature($requestData);
        
        // Prepare headers
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . PalmPayConfig::$appId,
            'Signature: ' . $signature,
            'CountryCode: ' . PalmPayConfig::$countryCode,
        ];
        
        // Build API endpoint URL
        $url = PalmPayConfig::getBaseUrl() . '/api/v2/bill-payment/item/query';
        
        // Send request
        $response = self::sendRequest($url, $headers, json_encode($requestData));
        
        // Parse and return response
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse API response: ' . json_last_error_msg());
        }
        
        // Handle specific error codes
        if ($result['respCode'] !== '00000000') {
            self::handleItemErrors($result['respCode'], $result['respMsg']);
        }
        
        return $result;
    }
    
    /**
     * Handle specific item query errors
     */
    private static function handleItemErrors($code, $message) {
        switch ($code) {
            case 'SBPINVALID_PARAMETER':
                throw new Exception('Invalid parameters: ' . $message);
            case 'SBPINVALID_SCENE_CODE':
                throw new Exception('Invalid scene code: ' . $message);
            case 'SBPBILLER_DISABLE':
                throw new Exception('Biller unavailable: ' . $message);
            default:
                throw new Exception('API Error: ' . $message . ' (Code: ' . $code . ')');
        }
    }
    
    /**
     * Generate a random nonce string
     */
    private static function generateNonceStr($length = 32) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $str;
    }
    
    /**
     * Send HTTP request to PalmPay API
     */
    private static function sendRequest($url, $headers, $body) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Curl error: ' . $error);
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('API request failed with HTTP code: ' . $httpCode);
        }
        
        return $response;
    }
}

$biller_id = test_input($_GET['biller_id']) ?? null;
$biller_icon = test_input($_GET['biller_icon']) ?? null;
$phone_number = test_input($_GET['phone_number']) ?? null;
$items = PalmPayBillerService::queryItems('data', $biller_id);
?>

<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php"; ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
          <?= $_SESSION['error'] ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>
      
      <button onclick="history.back()" class="btn btn-primary mb-3">Back</button>
      
      <div class="card shadow p-4">
        <div class="text-center mb-3">
          <img src="<?php echo htmlspecialchars($biller_icon); ?>" alt="Biller Icon" class="img-fluid" style="max-width: 100px;">
        </div>
        
        <h2 class="text-center bg-primary text-light h5">Data Subscription</h2>
        
        <form id="dataPlanForm" action="./_/ac_process_data_palmpay_live_test.php" method="post">
          <input type="hidden" name="biller_id" value="<?php echo htmlspecialchars($biller_id); ?>">
          
          <div class="form-group mt-3">
            <label for="phone_number">Phone Number</label>
            <input type="text" name="phone_number" id="phone_number" class="form-control" 
                   value="<?php echo htmlspecialchars($phone_number); ?>" placeholder="Enter Phone Number" required>
          </div>
          
          <div class="form-group mt-3">
            <label for="bouquet_code">Select Data Plan</label>
            <select name="bouquet_code" id="bouquet_code" class="form-control" required>
              <option value="">-- Select Data Plan --</option>
              <?php foreach ($items['data'] as $plan): ?>
                <option value="<?php echo htmlspecialchars($plan['itemId']); ?>" 
                        data-price="<?php echo htmlspecialchars($plan['amount'] / 100); ?>">
                  <?php echo htmlspecialchars($plan['itemName']); ?> - 
                  ₦<?php echo number_format($plan['amount'] / 100, 2); ?>
                  <?php if (!empty($plan['extInfo']['validity'])): ?>
                    (Valid for <?php echo htmlspecialchars($plan['extInfo']['validity']); ?>)
                  <?php endif; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-group mt-3">
            <label>Is this a ported number?</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="ported_number" id="ported_number_yes" value="true">
              <label class="form-check-label" for="ported_number_yes">Yes</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="ported_number" id="ported_number_no" value="false" checked>
              <label class="form-check-label" for="ported_number_no">No</label>
            </div>
          </div>
          
          <hr>
          
          <div class="mt-3">
            <div class="row">
              <div class="col-md-3 mb-2">
                <strong>Bundle Price:</strong> 
                <span id="priceDisplay">₦0.00</span>
                <input type="hidden" name="price" id="price" value="0">
              </div>
              
              <div class="col-md-3 mb-2">
                <strong>Bonus (<?= $bonus ?>%):</strong> 
                <span id="bonusDisplay">₦0.00</span>
                <input type="hidden" name="bonus_amount" id="bonus_amount" value="0">
                <input type="hidden" name="bonus_percentage" id="bonus_percentage" value="<?= $bonus ?>">
              </div>
              
              <div class="col-md-3 mb-2">
                <strong>Amount to Pay:</strong> 
                <span id="amountToPayDisplay">₦0.00</span>
                <input type="hidden" name="amount_to_pay" id="amount_to_pay" value="0">
              </div>
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
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Transaction</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Phone Number:</strong> <span id="modalPhoneNumber"></span></p>
        <p><strong>Data Plan:</strong> <span id="modalDataPlan"></span></p>
        <p><strong>Bundle Price:</strong> <span id="modalPrice">₦0.00</span></p>
        <p><strong>Discount Received (<?= $bonus ?>%):</strong> <span id="modalDiscount">₦0.00</span></p>
        <p><strong>Amount to Pay:</strong> <span id="modalAmountToPay">₦0.00</span></p>
        <p class="text-danger mt-3">Are you sure you want to proceed with this transaction?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmTransaction">Confirm</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Calculate prices when plan selection changes
    $('#bouquet_code').change(function() {
        calculatePrices();
    });
    
    // Confirm button click handler
    $('#confirmButton').click(function() {
        if (validateForm()) {
            updateModal();
            $('#confirmationModal').modal('show');
        }
    });
    
    // Final confirmation handler
    $('#confirmTransaction').click(function() {
        $('#dataPlanForm').submit();
    });
    
    function calculatePrices() {
        const selectedOption = $('#bouquet_code option:selected');
        const price = parseFloat(selectedOption.data('price')) || 0;
        const bonusPercentage = parseFloat($('#bonus_percentage').val()) / 100;
        const bonusAmount = price * bonusPercentage;
        const amountToPay = price - bonusAmount;
        
        // Update display
        $('#priceDisplay').text('₦' + price.toFixed(2));
        $('#bonusDisplay').text('₦' + bonusAmount.toFixed(2));
        $('#amountToPayDisplay').text('₦' + amountToPay.toFixed(2));
        
        // Update hidden fields
        $('#price').val(price.toFixed(2));
        $('#bonus_amount').val(bonusAmount.toFixed(2));
        $('#amount_to_pay').val(amountToPay.toFixed(2));
    }
    
    function validateForm() {
        if ($('#phone_number').val().trim() === '') {
            alert('Please enter a phone number');
            return false;
        }
        
        if ($('#bouquet_code').val() === '') {
            alert('Please select a data plan');
            return false;
        }
        
        return true;
    }
    
    function updateModal() {
        $('#modalPhoneNumber').text($('#phone_number').val());
        $('#modalDataPlan').text($('#bouquet_code option:selected').text());
        $('#modalPrice').text('₦' + $('#price').val());
        $('#modalDiscount').text('₦' + $('#bonus_amount').val());
        $('#modalAmountToPay').text('₦' + $('#amount_to_pay').val());
    }
});
</script>