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
      <h2 class="text-center bg-primary text-light h5">SETTINGS</h2>
      <div class="row py-3">
        <div class="col-md-12">
          <div class="card shadow p-4">
            <!-- User Information Section -->
            <!-- <h6>Update User Information</h6>
            <form>
              <div class="mb-3 d-none">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" placeholder="Enter your name">
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" placeholder="Enter your email">
              </div>
              <button type="submit" class="btn btn-primary">Update Information</button>
            </form>
            <hr> -->

            <!-- Change Password Section -->
            <h6>Change Password</h6>
            <form>
              <div class="mb-3">
                <!-- <label for="currentPassword" class="form-label">Current Password</label> -->
                <input type="password" class="form-control" id="currentPassword" placeholder="Enter current password">
              </div>
              <div class="mb-3">
                <!-- <label for="newPassword" class="form-label">New Password</label> -->
                <input type="password" class="form-control" id="newPassword" placeholder="Enter new password">
              </div>
              <div class="mb-3">
                <!-- <label for="confirmPassword" class="form-label">Confirm New Password</label> -->
                <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password">
              </div>
              <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
            <hr>

            <!-- Notification Preferences Section -->
            <!-- <h6>Notification Preferences</h6>
            <form>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                <label class="form-check-label" for="emailNotifications">
                  Email Notifications
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="smsNotifications">
                <label class="form-check-label" for="smsNotifications">
                  SMS Notifications
                </label>
              </div>
              <button type="submit" class="btn btn-primary">Save Preferences</button>
            </form>
            <hr> -->

            <!-- Other Settings Section -->
            <!-- <h6>Other Settings</h6>
            <form>
              <div class="mb-3">
                <label for="language" class="form-label">Language</label>
                <select class="form-select" id="language">
                  <option selected>English</option>
                  <option>Spanish</option>
                  <option>French</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary">Save Settings</button>
            </form> -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>