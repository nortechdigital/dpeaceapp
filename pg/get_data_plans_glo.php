<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$plansJson = file_get_contents('./glo/glo-data-plans.json');
$plansData = json_decode($plansJson, true);
$plans = $plansData['plans'];
?>
<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">
    <div class="container mt-4">
        <h2>Glo Data Plans</h2>
        
        <!-- Phone Number Input Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="phoneForm" class="row g-3">
                    <div class="col-md-6">
                        <label for="phoneNumber" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phoneNumber" name="phone_number"
                               placeholder="Enter recipient phone number" required
                               pattern="[0-9]{11}" title="Please enter a valid 11-digit phone number">
                    </div>
                    <div class="col-md-6">
                        <label for="network" class="form-label">Network</label>
                        <input type="text" class="form-control" id="network" name="network"
                               value="Glo" readonly>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="categoryFilter" class="form-label">Filter by Category:</label>
            <select class="form-select" id="categoryFilter">
                <option value="">All Categories</option>
                <?php
                $categories = array_unique(array_column($plans, 'category'));
                foreach ($categories as $category) {
                    echo "<option value=\"$category\">$category</option>";
                }
                ?>
            </select>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="plansTable">
                <thead>
                    <tr>
                        <th>Plan Name</th>
                        <th>Price (₦)</th>
                        <th>Data Volume</th>
                        <th>Validity</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plans as $plan): ?>
                    <tr data-category="<?= htmlspecialchars($plan['category']) ?>">
                        <td><?= htmlspecialchars($plan['plan_name']) ?></td>
                        <td><?= htmlspecialchars($plan['price']) ?></td>
                        <td><?= htmlspecialchars($plan['total_data_volume']) ?></td>
                        <td><?= htmlspecialchars($plan['validity']) ?> days</td>
                        <td><?= htmlspecialchars($plan['description']) ?></td>
                        <td>
                            <form method="post" action="./_/ac_process_data_glo.php" class="d-inline">
                                <input type="hidden" name="phone_number" id="hiddenPhone_<?= $plan['serial_number'] ?>">
                                <input type="hidden" name="plan_id" value="<?= htmlspecialchars($plan['ers_plan_id']) ?>">
                                <input type="hidden" name="plan_name" value="<?= htmlspecialchars($plan['plan_name']) ?>">
                                <input type="hidden" name="price" value="<?= htmlspecialchars($plan['price']) ?>">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    Buy Now
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
  </div>
</div>

<script>
// Update hidden phone field when main phone input changes
document.getElementById('phoneNumber').addEventListener('input', function() {
    const phoneValue = this.value;
    document.querySelectorAll('[id^="hiddenPhone_"]').forEach(el => {
        el.value = phoneValue;
    });
});

// Filter functionality
document.getElementById('categoryFilter').addEventListener('change', function() {
    const category = this.value;
    const rows = document.querySelectorAll('#plansTable tbody tr');
    
    rows.forEach(row => {
        if (category === '' || row.getAttribute('data-category') === category) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Validate phone number before form submission
document.querySelectorAll('form[action="./_/ac_process_data_glo.php"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        const phoneNumber = document.getElementById('phoneNumber').value;
        
        if (!phoneNumber) {
            alert('Please enter a phone number first');
            document.getElementById('phoneNumber').focus();
            e.preventDefault();
            return;
        }
        
        if (!/^[0-9]{11}$/.test(phoneNumber)) {
            alert('Please enter a valid 11-digit phone number');
            e.preventDefault();
            return;
        }
        
        if (!confirm(`Confirm purchase of ${this.plan_name.value} for ₦${this.price.value} for ${phoneNumber}?`)) {
            e.preventDefault();
        }
    });
});
</script>