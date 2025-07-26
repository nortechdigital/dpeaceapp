<?php
class PalmPayConfig {
    const TEST_ENV_URL = 'https://open-gw-daily.palmpay-inc.com';
    const PROD_ENV_URL = 'https://open-gw-prod.palmpay-inc.com';
    
    public static $appId = 'L250530142620642487801';
    public static $merchantPrivateKeyFile = './palmpay/private.pem'; // Path to private key file
    public static $palmPayPublicKeyFile = './palmpay/public.pem';    // Path to public key file
    public static $countryCode = 'NG'; // Nigeria by default
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
     * Query available billers for a specific scene code
     * 
     * @param string $sceneCode Business scenario code (e.g., 'airtime', 'data', 'betting')
     * @return array API response containing biller list
     * @throws Exception If the API request fails
     */
    public static function queryBillers($sceneCode) {
        // Prepare request data
        $requestData = [
            'requestTime' => round(microtime(true) * 1000), // Current timestamp in milliseconds
            'nonceStr' => self::generateNonceStr(), // Random string
            'version' => 'V2', // API version
            'sceneCode' => $sceneCode // Business scenario
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
        
        // Build API endpoint URL - ensure no double slashes
        $url = rtrim(PalmPayConfig::getBaseUrl(), '/') . '/api/v2/bill-payment/biller/query';
        
        // Send request
        $response = self::sendRequest($url, $headers, json_encode($requestData));
        
        // Parse and return response
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse API response: ' . json_last_error_msg());
        }
        
        return $result;
    }
    
    /**
     * Generate a random nonce string
     * 
     * @param int $length Length of the nonce string (default 32)
     * @return string Generated nonce string
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
     * 
     * @param string $url API endpoint URL
     * @param array $headers Request headers
     * @param string $body Request body (JSON encoded)
     * @return string API response
     * @throws Exception If the request fails
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

try {
    // Query airtime billers
    $response = PalmPayBillerService::queryBillers('airtime');
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}
?>
<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">

    <div class="container py-2">
      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= $_SESSION['success'] ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-primary alert-dismissible fade show" role="alert">
          <?= $_SESSION['error'] ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>
      
      <div class="card shadow p-4">
        <div class="text-center mb-3">
          <div class="d-flex justify-content-center flex-wrap">
            <img src="<?= $response['data'][0]['billerIcon'] ?? '' ?>" alt="<?= $response['data'][0]['billerName'] ?? '' ?>" class="img-fluid mx-2" style="width: 60px; height: auto;">
            <img src="<?= $response['data'][1]['billerIcon'] ?? '' ?>" alt="<?= $response['data'][1]['billerName'] ?? '' ?>" class="img-fluid mx-2" style="width: 60px; height: auto;">
			      <img src="<?= $response['data'][2]['billerIcon'] ?? '' ?>" alt="<?= $response['data'][2]['billerName'] ?? '' ?>" class="img-fluid mx-2" style="width: 60px; height: auto;">
			      <img src="<?= $response['data'][3]['billerIcon'] ?? '' ?>" alt="<?= $response['data'][3]['billerName'] ?? '' ?>" class="img-fluid mx-2" style="width: 60px; height: auto;">
          </div>
        </div>
        <h2 class="text-center bg-primary text-light h5">Airtime Top-Up</h2>
        <form id="airtimeForm" method="POST">
      	  <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
      	  <input type="hidden" name="tx_ref" value="<?= uniqid('airtime_', true) ?>">
          <div class="form-group">
            <label for="provider">Select Provider</label>
            <select name="provider" id="provider" class="form-control" onchange="updateFormAction()" required>
              <option value="">Select Provider</option>
              <option value="<?= $response['data'][0]['billerId'] ?? '' ?>"><?= $response['data'][0]['billerName'] ?? '' ?></option>
              <option value="<?= $response['data'][1]['billerId'] ?? '' ?>"><?= $response['data'][1]['billerName'] ?? '' ?></option>
              <option value="<?= $response['data'][2]['billerId'] ?? '' ?>"><?= $response['data'][2]['billerName'] ?? '' ?></option>
              <option value="<?= $response['data'][3]['billerId'] ?? '' ?>"><?= $response['data'][3]['billerName'] ?? '' ?></option>
            </select>
            <div class="invalid-feedback">Please select a provider</div>
          </div>
          
          <div class="form-group mt-3">
            <label for="phone_number">Phone Number</label>
            <input type="tel" name="phone_number" id="phone_number" class="form-control" 
                   placeholder="Enter 11-digit Phone Number" 
                   pattern="[0-9]{11}" 
                   title="Please enter exactly 11 digits (numbers only)"
                   required>
            <div class="invalid-feedback">Please enter a valid 11-digit phone number</div>
          </div>

          <div class="form-group mt-3">
            <label for="amount">Amount</label>
            <input type="number" name="amount" id="amount" class="form-control" 
                   placeholder="Enter Amount" 
                   min="50" 
                   required>
            <div class="invalid-feedback">Please enter an amount (minimum ₦50)</div>
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
              <div class="col-lg-3">Cost Price: &#8358;<span name="price" id="price">0</span></div>
              <div class="col-lg-2">Bonus: <span id="bonusText">0%</span></div>
              <div class="col-lg-4">Discount Received: &#8358;<span name="profit" id="profit">0</span></div>
              <div class="col-lg-3">Amount to Pay: &#8358;<span name="discounted_price" id="discounted_price">0</span></div>
            </div>
            <input type="hidden" name="discounted_price" id="hidden_discounted_price" value="0">
          </div>
          <hr>
          
          <div class="form-group mt-3 text-center">
            <button type="button" class="btn btn-primary w-100" id="confirmButton">Top Up</button>
          </div>
        </form>

        <!-- Modal -->
        <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header justify-content-center">
                  <h5 class="modal-title">Confirm Transaction</h5>
              </div>
              <div class="modal-body justify-content-between">
                <p>Network Provider: <strong><span id="modalProvider"></span></strong></p>
                <p>Phone Number: <strong><span id="modalPhoneNumber"></span></strong></p>
                <p>Amount: <strong>&#8358;<span id="modalAmount"></span></strong></p>
                <p>Discount Received: <strong>&#8358;<span id="modalDiscount"></span></strong></p>
                <p>Amount to Pay: <strong>&#8358;<span id="modalAmountToPay"></span></strong></p>
                <p class="text-danger">Are you sure you want to proceed with this transaction?</p>
              </div>
              <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" id="modalSubmitButton">OK</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Phone number validation
document.getElementById('phone_number').addEventListener('input', function(e) {
    let phoneNumber = e.target.value.replace(/\D/g, '');
    if (phoneNumber.length > 11) {
        phoneNumber = phoneNumber.substring(0, 11);
    }
    e.target.value = phoneNumber;
    
    if (phoneNumber.length === 11) {
        e.target.classList.remove('is-invalid');
        e.target.classList.add('is-valid');
    } else {
        e.target.classList.remove('is-valid');
        e.target.classList.add('is-invalid');
    }
});

// Form validation function
function validateForm() {
    const provider = document.getElementById('provider');
    const phoneNumber = document.getElementById('phone_number');
    const amount = document.getElementById('amount');
    let isValid = true;

    // Validate provider
    if (!provider.value) {
        provider.classList.add('is-invalid');
        isValid = false;
    } else {
        provider.classList.remove('is-invalid');
    }

    // Validate phone number
    if (!phoneNumber.value || phoneNumber.value.length !== 11 || !/^[0-9]{11}$/.test(phoneNumber.value)) {
        phoneNumber.classList.add('is-invalid');
        isValid = false;
    } else {
        phoneNumber.classList.remove('is-invalid');
    }

    // Validate amount
    if (!amount.value || parseFloat(amount.value) < 50) {
        amount.classList.add('is-invalid');
        isValid = false;
    } else {
        amount.classList.remove('is-invalid');
    }

    return isValid;
}

// Confirm button click handler
document.getElementById('confirmButton').addEventListener('click', function() {
    if (validateForm()) {
        const provider = document.getElementById('provider').options[document.getElementById('provider').selectedIndex].text;
        const phoneNumber = document.getElementById('phone_number').value;
        const amount = document.getElementById('amount').value;
        const discount = document.getElementById('profit').textContent;
        const amountToPay = document.getElementById('discounted_price').textContent;

        document.getElementById('modalProvider').textContent = provider;
        document.getElementById('modalPhoneNumber').textContent = phoneNumber;
        document.getElementById('modalAmount').textContent = amount;
        document.getElementById('modalDiscount').textContent = discount;
        document.getElementById('modalAmountToPay').textContent = amountToPay;

        $('#confirmationModal').modal('show');
    } else {
        // Scroll to the first invalid field
        const firstInvalid = document.querySelector('.is-invalid');
        if (firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalid.focus();
        }
    }
});

// Modal submit button handler
document.getElementById('modalSubmitButton').addEventListener('click', function() {
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
    document.getElementById('airtimeForm').submit();
});

// Rest of your existing scripts (updateFormAction, calculatePrices, etc.)
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form action
    updateFormAction();
    
    // Initialize price calculator
    const amountInput = document.getElementById('amount');
    const providerSelect = document.getElementById('provider');
    
    amountInput.addEventListener('input', calculatePrices);
    providerSelect.addEventListener('change', function() {
        updateFormAction();
        calculatePrices();
    });
    
    function calculatePrices() {
        const amount = parseFloat(amountInput.value) || 0;
        const provider = providerSelect.value;

        let discountRate = 0.05; // 5% discount for all providers
        let bonus = 5; // 5% bonus for all providers

        const costPrice = amount;
        const discountReceived = amount * discountRate;
        const amountToPay = amount - discountReceived;

        document.getElementById('price').textContent = costPrice.toFixed(2);
        document.getElementById('profit').textContent = discountReceived.toFixed(2);
        document.getElementById('discounted_price').textContent = amountToPay.toFixed(2);
        document.getElementById('bonusText').textContent = `${bonus}%`;
        document.getElementById('hidden_discounted_price').value = amountToPay.toFixed(2);
    }
});

function updateFormAction() {
  const provider = document.getElementById('provider').value;
  const form = document.getElementById('airtimeForm');
  if (provider == 'GLO') {
    form.action = './_/ac_process_airtime_glo.php';
  } else {
    form.action = './_/ac_process_airtime_palmpay.php';
  }
}
</script>