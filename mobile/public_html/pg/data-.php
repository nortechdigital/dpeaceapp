<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}
?>

<?php
// Securely store API token
$apiToken = '886|XNO94Wyb1tdNq9LKO6gYlgY8paT65TM7bcDTT7Kt'; // Replace with your actual API token

// Initialize cURL session
$curl = curl_init();

// Set cURL options
curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://www.airtimenigeria.com/api/v1/data/plans',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer $apiToken",
        "Accept: application/json",
        "Content-Type: application/json"
    ),
));

// Execute the request and store the response
$response = curl_exec($curl);

// Check for errors
if (curl_errno($curl)) {
    echo 'cURL Error: ' . curl_error($curl);
} else {
    // Convert JSON response to an associative array
    $dataPlans = json_decode($response, true);

    // Display response in a readable format
    echo "<pre>";
    // print_r($dataPlans);
    // print_r($dataPlans['data']);
    echo "</pre>";
    
}

// Close cURL session
curl_close($curl);
?>
<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
      <h2 class="text-center bg-primary text-light h5">DATA SUBSCRIPTION</h2>
      <form action="./_/ac_process_subscription.php" method="post">
        <div class="mb-2">
          <label for="phone" class="form-label">Phone:</label>
          <input type="text" name="phone" id="phone" class="form-control" required>
        </div>
        <div class="mb-2">
          <label for="packageCode" class="form-label">Plan Summary:</label>
          <select name="package_code" id="packageCode" class="form-select">
            <?php foreach ($dataPlans['data'] as $plan) : ?>
              <option value="<?= $plan['package_code']; ?>"><?= $plan['plan_summary']; ?> &#8358;<?= number_format($plan['regular_price']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <?php if ($status == 0): ?>
            <a href="./?page=verify_account" class="btn btn-primary">Verify Account</a>
          <?php else: ?>
            <button type="submit" class="btn btn-primary">Subscribe</button>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
</div>
