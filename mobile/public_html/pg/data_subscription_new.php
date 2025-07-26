<?php
// filepath: /c:/xampp/htdocs/dpeace/pg/data_subscription.php
include "../conn.php";
// session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

// Define the getProviderDetails function
function getProviderDetails($provider) {
    global $conn;
    $query = "SELECT * FROM providers WHERE name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $provider);
    $stmt->execute();
    $result = $stmt->get_result();
    $details = $result->fetch_assoc();
    $stmt->close();
    return $details;
}

// Get provider details
$provider = $_GET['provider'] ?? '';
$providerDetails = getProviderDetails($provider); // Assuming this function exists and fetches provider details

// Get discount from settings table
$discount = 0;
$query = "SELECT discount FROM settings WHERE provider = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $provider);
$stmt->execute();
$stmt->bind_result($discount);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Subscription</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script>
    function updatePrices() {
      const bundleSelect = document.getElementById('smile_bundle_code');
      const selectedOption = bundleSelect.options[bundleSelect.selectedIndex];
      const price = parseFloat(selectedOption.getAttribute('data-price'));
      const discount = <?php echo $discount; ?>;
      const discountedPrice = price - (price * discount / 100);
      const profit = price - discountedPrice;

      document.getElementById('price').innerText = price.toFixed(2);
      document.getElementById('discounted_price').innerText = discountedPrice.toFixed(2);
      document.getElementById('profit').innerText = profit.toFixed(2);
    }
  </script>
</head>
<body>
<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
      <button onclick="history.back()" class="btn btn-primary mb-3">Back</button>
      <?php if ($providerDetails): ?>
        <div class="card shadow p-4">
          <img src="<?php echo $providerDetails['logo']; ?>" alt="<?php echo $providerDetails['name']; ?> Logo" class="img-fluid mb-3 d-block mx-auto" width="80px" height="auto">
          <h2 class="text-center bg-primary text-light h5"><?php echo $providerDetails['name']; ?> SUBSCRIPTION</h2>
          <form action="process_subscription.php" method="post">
            <input type="hidden" name="provider" value="<?php echo $provider; ?>">
            <?php if ($provider === 'smile'): ?>
              <input type="text" name="account_value" id="account_value" class="form-control mt-3" placeholder="Enter Phone Number or Account Number">
              <select name="smile_bundle_code" id="smile_bundle_code" class="form-control mt-3" onchange="updatePrices()">
                <option value="">Select Bundle</option>
                <option value="1GB" data-price="1000">1GB - &#8358;1000</option>
                <option value="2GB" data-price="1500">2GB - &#8358;1500</option>
                <option value="3GB" data-price="2200">3GB - &#8358;2200</option>
                <option value="5GB" data-price="3000">5GB - &#8358;3000</option>
                <option value="7GB" data-price="4000">7GB - &#8358;4000</option>
              </select>
              <div class="mt-3">
                <p>Price: &#8358;<span id="price">0</span></p>
                <p>Amount to Pay: &#8358;<span id="discounted_price">0</span></p>
                <?php if ($discount > 0): ?>
                  <p>Discount: <?php echo $discount; ?>%</p>
                <?php endif; ?>
                <p>Profit: &#8358;<span id="profit">0</span></p>
              </div>
            <?php else: ?>
              <!-- Other provider form fields -->
            <?php endif; ?>
            <button type="submit" class="btn btn-primary mt-3">Subscribe</button>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>