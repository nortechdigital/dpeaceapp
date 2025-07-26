<?php
$plansJson = file_get_contents('./glo/glo-data-plans.json');
$plansData = json_decode($plansJson, true);
$plans = $plansData['plans'] ?? [];

$logo = './img/logo/glo_logo.jpg';
$name = 'Glo';
$bonusPercent = 5;

$categories = array_unique(array_column($plans, 'category'));
?>

<div class="row">
    <div class="col-lg-2">
        <?php include "./inc/sidebar.php"; ?>
    </div>
    <div class="col-lg-10">
      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-primary alert-dismissible fade show" role="alert">
          <?= $_SESSION['error'] ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>
        <div class="container py-2">
            <button onclick="history.back()" class="btn btn-primary mb-3">Back</button>
            <div class="card shadow p-4">
                <div class="text-center mb-3">
                    <img src="<?= htmlspecialchars($logo) ?>" alt="GLO" class="img-fluid mx-2" width="80">
                </div>
                <h2 class="text-center bg-primary text-light h5">Data Subscription</h2>
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="post" action="./_/ac_process_data_glo.php" id="buyForm">
      						<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="phone_number" id="hiddenPhone">
                            <input type="hidden" name="plan_id" id="hiddenPlanId">
                            <input type="hidden" name="plan_name" id="hiddenPlanName">
                            <input type="hidden" name="price" id="hiddenPlanPrice">
                            <input type="hidden" name="discounted_price" id="hiddenDiscountedPrice" value="0">
                            <input type="hidden" name="network" value="Glo">

                            <label for="phoneNumber" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control mb-3" id="phoneNumber" name="phone_number"
                                   placeholder="Enter recipient phone number" required pattern="[0-9]{11}"
                                   title="Please enter a valid 11-digit phone number" inputmode="numeric" maxlength="11"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,11);">

                            <div class="mb-3">
                                <label for="categoryFilter" class="form-label">Select Category:</label>
                                <select class="form-select" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="my-3" id="planDropdownWrapper" style="display: none;">
                                <label for="planDropdown" class="form-label">Select Data Plan:</label>
                                <select class="form-select mb-3" id="planDropdown">
                                    <option value="">-- Choose a plan --</option>
                                    <?php foreach ($plans as $plan): ?>
                                        <option
                                            value="<?= htmlspecialchars($plan['ers_plan_id']) ?>"
                                            data-plan_name="<?= htmlspecialchars($plan['plan_name']) ?>"
                                            data-price="<?= htmlspecialchars($plan['price']) ?>"
                                            data-volume="<?= htmlspecialchars($plan['total_data_volume']) ?>"
                                            data-validity="<?= htmlspecialchars($plan['validity']) ?>"
                                            data-description="<?= htmlspecialchars($plan['description']) ?>"
                                            data-category="<?= htmlspecialchars($plan['category']) ?>"
                                        >
                                            ₦<?= htmlspecialchars($plan['price']) ?> - <?= htmlspecialchars($plan['total_data_volume']) ?> (<?= htmlspecialchars($plan['validity']) ?> days)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="planDetails" class="mb-3 d-none" style="display:none;">
                                <strong>Plan:</strong> <span id="detailName"></span><br>
                                <strong>Price:</strong> ₦<span id="detailPrice"></span><br>
                                <strong>Data Volume:</strong> <span id="detailVolume"></span><br>
                                <strong>Validity:</strong> <span id="detailValidity"></span> days<br>
                                <strong>Description:</strong> <span id="detailDescription"></span>
                            </div>
                            <hr>
                            <div class="mt-3">
                                <div class="row">
                                    <div class="col-lg-3">Cost Price: ₦<span id="displayPrice">0</span></div>
                                    <div class="col-lg-2">Bonus: <span><?= $bonusPercent ?>%</span></div>
                                    <div class="col-lg-4">Discount Received: ₦<span id="displayProfit">0</span></div>
                                    <div class="col-lg-3">Amount to Pay: ₦<span id="displayDiscountedPrice">0</span></div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary w-100 mt-3" id="buyNowBtn" data-bs-toggle="modal" data-bs-target="#confirmPurchaseModal">Buy Now</button>

                            <div class="modal fade" id="confirmPurchaseModal" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header justify-content-center">
                                            <h5 class="modal-title">Confirm Transaction</h5>
                                        </div>
                                        <div class="modal-body" id="modalPurchaseDetails">
                                            <p>Phone Number: <span id="modalPhoneNumber"></span></p>
                                            <p>Data Plan: <span id="modalDataPlan"></span></p>
                                            <p>Bundle Price: ₦<span id="modalPrice"></span></p>
                                            <p>Discount Received: ₦<span id="modalProfit"></span></p>
                                            <p>Amount to Pay: ₦<span id="modalDiscountedPrice"></span></p>
                                            <p class="text-danger">Are you sure you want to proceed with this transaction?</p>
                                        </div>
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-primary" id="confirmModalBtn">OK</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Show/hide dropdown on category filter
    document.getElementById('categoryFilter').addEventListener('change', function () {
        const selectedCategory = this.value;
        const planDropdown = document.getElementById('planDropdown');
        const planWrapper = document.getElementById('planDropdownWrapper');
        const planDetails = document.getElementById('planDetails');

        planWrapper.style.display = selectedCategory ? '' : 'none';
        planDetails.style.display = 'none';

        for (let i = 0; i < planDropdown.options.length; i++) {
            const option = planDropdown.options[i];
            option.style.display = (i === 0 || !selectedCategory || option.getAttribute('data-category') === selectedCategory) ? '' : 'none';
        }

        planDropdown.selectedIndex = 0;
        resetPriceFields();
    });

    // Handle plan selection
    document.getElementById('planDropdown').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        if (!this.value) {
            document.getElementById('planDetails').style.display = 'none';
            resetPriceFields();
            return;
        }

        const price = parseFloat(selected.getAttribute('data-price')) || 0;
        const bonus = <?= $bonusPercent ?>;
        const profit = Math.round(price * bonus / 100);
        const discounted = price - profit;

        // Fill plan details
        document.getElementById('detailName').textContent = selected.getAttribute('data-plan_name');
        document.getElementById('detailPrice').textContent = price;
        document.getElementById('detailVolume').textContent = selected.getAttribute('data-volume');
        document.getElementById('detailValidity').textContent = selected.getAttribute('data-validity');
        document.getElementById('detailDescription').textContent = selected.getAttribute('data-description');
        document.getElementById('planDetails').style.display = '';

        // Fill hidden fields
        document.getElementById('hiddenPlanId').value = selected.value;
        document.getElementById('hiddenPlanName').value = selected.getAttribute('data-plan_name');
        document.getElementById('hiddenPlanPrice').value = price;
        document.getElementById('hiddenDiscountedPrice').value = discounted;

        // Display price summary
        document.getElementById('displayPrice').textContent = price;
        document.getElementById('displayProfit').textContent = profit;
        document.getElementById('displayDiscountedPrice').textContent = discounted;
    });

    function resetPriceFields() {
        document.getElementById('displayPrice').textContent = '0';
        document.getElementById('displayProfit').textContent = '0';
        document.getElementById('displayDiscountedPrice').textContent = '0';
        document.getElementById('hiddenDiscountedPrice').value = '0';
        document.getElementById('planDetails').style.display = 'none';
    }

    // Sync phone number to hidden field
    document.getElementById('phoneNumber').addEventListener('input', function () {
        document.getElementById('hiddenPhone').value = this.value;
    });

    // Buy Now button opens modal
    document.getElementById('buyNowBtn').addEventListener('click', function () {
        const phoneNumber = document.getElementById('phoneNumber').value;
        const planName = document.getElementById('hiddenPlanName').value;
        const price = document.getElementById('hiddenPlanPrice').value;
        const profit = document.getElementById('displayProfit').textContent;
        const discounted = document.getElementById('displayDiscountedPrice').textContent;

        if (!phoneNumber || !/^[0-9]{11}$/.test(phoneNumber)) {
            alert('Please enter a valid 11-digit phone number');
            return;
        }

        if (!planName) {
            alert('Please select a data plan');
            return;
        }

        document.getElementById('modalPhoneNumber').textContent = phoneNumber;
        document.getElementById('modalDataPlan').textContent = planName;
        document.getElementById('modalPrice').textContent = price;
        document.getElementById('modalProfit').textContent = profit;
        document.getElementById('modalDiscountedPrice').textContent = discounted;

        const modal = new bootstrap.Modal(document.getElementById('confirmPurchaseModal'));
        modal.show();
    });

    // Confirm button submits form
    document.getElementById('confirmModalBtn').addEventListener('click', function () {
        const okButton = this;
        okButton.disabled = true;
        okButton.textContent = 'Processing...';

        // Optional: also disable Cancel button to prevent interaction
        const cancelButton = document.querySelector('#confirmPurchaseModal .btn-secondary');
        if (cancelButton) cancelButton.disabled = true;

        // Submit the form
        document.getElementById('buyForm').submit();
    });

</script>
