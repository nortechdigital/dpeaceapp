<?php
if (!isset($_SESSION['user_id'])) {
  header("Location: ./?page=login");
  exit;
}
?>

<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">  
    <div class="container py-2">
      <div class="card shadow p-4">
        <div class="text-center mb-3">
            <div class="d-flex justify-content-center flex-wrap">
            <img src="img/logo/glo_logo.jpg" alt="Glo" class="img-fluid mx-2" style="width: 60px; height: auto;">
            <img src="img/logo/airtel_logo.png" alt="Airtel" class="img-fluid mx-2" style="width: 60px; height: auto;">
            <img src="img/logo/mtn_logo.png" alt="MTN" class="img-fluid mx-2" style="width: 60px; height: auto;">
            <img src="img/logo/9moble_logo.png" alt="9mobile" class="img-fluid mx-2" style="width: 60px; height: auto;">
            </div>
        </div>
        <h2 class="text-center bg-primary text-light h5">Airtime Top-Up</h2>
        <form id="airtimeForm" method="POST">
          <div class="form-group">
            <label for="provider">Select Provider</label>
            <select name="provider" id="provider" class="form-control" onchange="updateFormAction()">
              <option value="">Select Provider</option>
              <option value="9Mobile-AIRTIME">9mobile</option>
              <option value="AIRTEL-AIRTIME">Airtel</option>
              <option value="GLO-AIRTIME">Glo</option>
              <option value="MTN-AIRTIME">MTN</option>
            </select>
          </div>
          
          <div class="form-group mt-3">
            <label for="phone_number">Phone Number</label>
            <input type="text" name="phone_number" id="phone_number" class="form-control" placeholder="Enter Phone Number" required>
          </div>
          <div class="form-group mt-3">
            <label for="amount">Amount</label>
            <input type="number" name="amount" id="amount" class="form-control" placeholder="Enter Amount" required>
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
                <p>Network Provider: <strong><span id="modalProvider"></strong></span></p>
                <p>Phone Number: <strong><span id="modalPhoneNumber"></strong></span></p>
                  <p>Amount: <strong>&#8358;<span id="modalAmount"></strong> </span></p>
                  <p>Discount Received: <strong>&#8358;<span id="modalDiscount"></strong></span></p>
                  <p>Amount to Pay: <strong>&#8358;<span id="modalAmountToPay"></strong></span></p>
                  <!-- <p>Note: You will receive a <span id="modalBonus"></span> bonus on this transaction.</p> -->
                  <p class="text-danger">Are you sure you want to proceed with this transaction?</p>
                </div>
        
              <div class="modal-footer justify-content-center">
                  <button type="button" class="btn btn-primary" id="modalSubmitButton">OK</button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#confirmationModal').modal('hide');">Cancel</button>
              </div>
          </div>
        </div>
        
      </div>
    </div>
  </div>
</div>

<script>
  function updateFormAction() {
    const provider = document.getElementById('provider').value;
    const form = document.getElementById('airtimeForm');
    if (provider == '9Mobile-AIRTIME' || provider == 'MTN-AIRTIME') {
      form.action = './_/ac_process_airtime_vas2nets.php';
    } else if (provider == 'AIRTEL-AIRTIME') {
      form.action = './_/ac_process_airtime_vas2nets.php';
    } else if (provider == 'GLO-AIRTIME') {
      form.action = './_/ac_process_airtime_vas2nets.php';
    } else {
      form.action = './_/ac_process_airtime.php';
    }
  }

  // Form validation
  document.getElementById('airtimeForm').addEventListener('submit', function (event) {
      const phoneNumber = document.getElementById('phone_number').value;
      const amount = document.getElementById('amount').value;

      if (!phoneNumber || !amount) {
          alert('Please fill out all fields.');
          event.preventDefault(); // Prevent form submission
      }
  });

  document.addEventListener('DOMContentLoaded', function () {
    const amountInput = document.getElementById('amount');
    const providerSelect = document.getElementById('provider');
    const priceDisplay = document.getElementById('price');
    const profitDisplay = document.getElementById('profit');
    const discountedPriceDisplay = document.getElementById('discounted_price');
    const bonusText = document.getElementById('bonusText');

    amountInput.addEventListener('input', calculatePrices);
    providerSelect.addEventListener('change', calculatePrices);

    function calculatePrices() {
      const amount = parseFloat(amountInput.value) || 0;
      const provider = providerSelect.value;

      let discountRate = 0;
      let bonus = 0;
      if (provider === 'MTN-AIRTIME') { 
        discountRate = 0.05; 
        bonus = 5;
      } else if (provider === '9Mobile-AIRTIME') {
        discountRate = 0.05; 
        bonus = 5;
      } else if (provider === 'AIRTEL-AIRTIME') { 
        discountRate = 0.05; 
        bonus = 5;
      } else if (provider === 'GLO-AIRTIME') { 
        discountRate = 0.05; 
        bonus = 5; 
      } else {
        bonus = 0;
      }

      const costPrice = amount;
      const discountReceived = amount * discountRate;
      const amountToPay = amount - discountReceived;

      priceDisplay.textContent = costPrice.toFixed(2);
      profitDisplay.textContent = discountReceived.toFixed(2);
      discountedPriceDisplay.textContent = amountToPay.toFixed(2);
      if (bonusText) {
        bonusText.textContent = `${bonus}%`;
      }
    }
    // Initialize on load
    calculatePrices();
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

  document.getElementById('confirmButton').addEventListener('click', function () {
    const phoneNumber = document.getElementById('phone_number').value;
    const provider = document.getElementById('provider').options[document.getElementById('provider').selectedIndex].text;
    const amount = document.getElementById('amount').value;
    const discount = document.getElementById('profit').textContent;
    const amountToPay = document.getElementById('discounted_price').textContent;

    document.getElementById('modalPhoneNumber').textContent = phoneNumber;
    document.getElementById('modalProvider').textContent = provider;
    document.getElementById('modalAmount').textContent = amount;
    document.getElementById('modalDiscount').textContent = discount;
    document.getElementById('modalAmountToPay').textContent = amountToPay;

    const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal')); // Ensure proper initialization
    confirmationModal.show();
  });

  document.getElementById('modalSubmitButton').addEventListener('click', function () {
    document.getElementById('airtimeForm').submit();
  });

  document.getElementById('confirmButton').addEventListener('click', function () {
  const phoneNumber = document.getElementById('phone_number').value;
  const provider = document.getElementById('provider').options[document.getElementById('provider').selectedIndex].text;
  const amount = document.getElementById('amount').value;
  const discount = document.getElementById('profit').textContent;
  const amountToPay = document.getElementById('discounted_price').textContent;
  const bonus = document.querySelector('.col-lg-2 span').textContent;
   
  document.getElementById('modalPhoneNumber').textContent = phoneNumber;
  document.getElementById('modalProvider').textContent = provider;
  document.getElementById('modalAmount').textContent = amount;
  document.getElementById('modalDiscount').textContent = discount;
  document.getElementById('modalAmountToPay').textContent = amountToPay;
  document.getElementById('modalBonus').textContent = bonus;

  const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
  confirmationModal.show();
  });
</script>