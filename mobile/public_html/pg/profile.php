<?php
if (!isset($_SESSION['user_id'])) {
  header("Location: ./?page=login");
  exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user status
$query = "SELECT status FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($status);
$stmt->fetch();
$stmt->close();

// Prepare and execute the query
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch KYC status
$query = "SELECT kyc_status FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
  die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($kyc_status);
$stmt->fetch();
$stmt->close();

// Check for a successful update message
$update_success = isset($_GET['update']) && $_GET['update'] == 'success';

?>
<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
      <h2 class="text-center bg-primary text-light h5">PROFILE</h2>
      <?php if ($update_success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          Profile updated successfully.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      <div class="row py-3">
        <div class="col-md-8 offset-md-2">
          <div class="card shadow p-4">
            <form action="./_/ac_update_profile.php" method="post" class="card p-3" style="max-width:500px;margin:auto">
      			<?php if ($kyc_status == 1): ?>
                <div class="alert alert-warning" role="alert">
                  Your account is not verified. Please <a href="?page=kyc" class="">Click Here</a> to verify your account to access all features.
                </div>
                <?php else: ?>
                <div class="alert alert-success" role="alert">
                  KYC Verification Sucessfull.
                </div>
              <?php endif; ?>
              <div class="mb-3 text-center">
                <img id="profileImagePreview" src="./img/image.png" alt="User Profile" class="img-thumbnail" style="width: 150px; height: 150px; border: none;">
                <div class="mt-3">
                  <label for="profileImage" class="form-label">Change Profile Image</label>
                  <input type="file" class="form-control" id="profileImage" onchange="previewImage(event)">
                </div>
              </div>
              <div class="mb-2">
                <label for="firstname" class="form-label">Firstname</label>
                <input type="text" name="firstname" id="firstname" value="<?= htmlspecialchars($user['firstname'], ENT_QUOTES, 'UTF-8') ?>" class="form-control" required>
              </div>
              <div class="mb-2">
                <label for="lastname" class="form-label">Lastname</label>
                <input type="text" name="lastname" id="lastname" value="<?= htmlspecialchars($user['lastname'], ENT_QUOTES, 'UTF-8') ?>" class="form-control" required>
              </div>
              <div class="mb-2">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>" class="form-control" required>
              </div>
              <div class="mb-2">
                <label for="username" class="form-label">Username</label>
                <input type="username" name="username" id="username" value="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>" class="form-control" required>
              </div>
              <div class="mb-2">
                <label for="phone" class="form-label">Phone</label>
                <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8') ?>" class="form-control" pattern="^\+?[0-9]{10,15}$" required>
              </div>
                <div class="mb-2">
                <label for="address" class="form-label">Address</label>
                <input type="tel" name="address" id="address" value="<?= htmlspecialchars($user['address'], ENT_QUOTES, 'UTF-8') ?>" class="form-control">
              </div>
              <div class="d-grid mb-2">
                  
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
function previewImage(event) {
  const reader = new FileReader();
  reader.onload = function() {
    const output = document.getElementById('profileImagePreview');
    output.src = reader.result;
  };
  reader.readAsDataURL(event.target.files[0]);
}
</script>
