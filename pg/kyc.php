<?php
 
if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verificationType = $_POST['verificationType'];
    $id_number = $_POST['nin'];
    $address = $_POST['address'];

    $query = "UPDATE users SET verificationType = ?, id_number = ?, address = ?, kyc_status = '0' WHERE id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("sssi", $verificationType, $id_number, $address, $user_id);
    if ($stmt->execute()) {
        echo "<script>alert('KYC update successful');</script>";
    } else {
        echo "<script>alert('KYC update not successful');</script>";
    }
    $stmt->close();
}

?>
<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
      <h2 class="text-center bg-primary text-light h5">KYC Verification</h2>
      <div class="row py-3">
        <div class="col-md-8 offset-md-2">
          <div class="card shadow p-4">
            <form method="POST">
              
              <fieldset>
                        <div class="form-group mb-3">
                            <label for="actype" class="form-label mb-2"><strong>Verification Type</strong></label>  
                            <select id="verificationType" name="verificationType" class="form-control mb-2" required>
                                <option value="" disabled="" selected="">Select Verification Type</option> 
                                <option value='nin'>NIN</option>
                                <!-- <option value='bvn'>BVN</option> -->
                            </select>
                        </div>
                        
                        <div class="form-group mb-3" id="ninDiv" style="display:none;">
                            <label for="nin" class="mb-2"><strong>NIN Number</strong></label>
                            <input type="text" name="nin" id="nin" class="form-control" placeholder="Enter NIN Number">
                        </div>

                        <!-- <div class="form-group mt-b" id="bvnDiv" style="display:none;">
                            <label for="bvn" class="mb-2"><strong>BVN Number</strong></label>
                            <input type="text" name="id_number" id="bvn" class="form-control" placeholder="Enter BVN">
                        </div> -->

                        <script>
                            document.getElementById('verificationType').addEventListener('change', function() {
                                var phoneNumberDiv = document.getElementById('ninDiv');
                                var accountNumberDiv = document.getElementById('bvnDiv');
                                if (this.value === 'nin') {
                                    phoneNumberDiv.style.display = 'block';
                                    accountNumberDiv.style.display = 'none';
                                } else if (this.value === 'bvn') {
                                    phoneNumberDiv.style.display = 'none';
                                    accountNumberDiv.style.display = 'block';
                                } else {
                                    phoneNumberDiv.style.display = 'none';
                                    accountNumberDiv.style.display = 'none';
                                }
                            });
                        </script>
                <label for="address" class="form-label">Address</label>
                <textarea name="address" id="address" class="form-control" rows="3" required></textarea>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Submit KYC</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
