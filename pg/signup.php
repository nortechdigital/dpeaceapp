<?php

// Generate CAPTCHA if not already set
if (!isset($_SESSION['captcha'])) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789qwertyuiopasdfghjklzxcvbnm';
    $_SESSION['captcha'] = substr(str_shuffle($chars), 0, 6);
} else {
	$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789qwertyuiopasdfghjklzxcvbnm';
    $_SESSION['captcha'] = substr(str_shuffle($chars), 0, 6);
}
?>
<section class="">
  <div class="container py-5">
    <div class="row">
    <div class="col-lg-6 d-none d-lg-block ">
        <img src="./img/dpeace-app.png" alt="Site Logo" class="img-fluid" style="max-width: 90%; height: auto;"><br>
        <h2 class="mb-4 text-dark">Better Life Begins with You!</h3>
      </div>
      <div class="col-lg">
<?php if(isset($_SESSION['error'])): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
  <?= $_SESSION['error'] ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['error']); endif; ?>
        <form action="./_/ac_signup.php" method="post" class="card">
          <div class="card-body">
            <div class="mb-2 text-center">
              <h3>Sign Up</h3>
            </div>
            <hr>
            <div class="mb-2">
              <label for="firstName" class="form-label">Firstname</label>
              <input type="text" name="firstname" id="firstName" class="form-control" maxlength=12 required>
            </div>
            <div class="mb-2">
              <label for="lastName" class="form-label">Lastname</label>
              <input type="text" name="lastname" id="lastName" class="form-control" maxlength=12 required>
            </div>
            <div class="mb-2">
              <label for="email" class="form-label">Email</label>
              <input type="email" name="email" id="email" class="form-control" maxlength=24 required>
            </div>
            <div class="mb-2">
              <label for="phone" class="form-label">Phone</label>
              <input type="tel" name="phone" id="phone" class="form-control" maxlength=11 required>
            </div>
            <div class="mb-2">
              <label for="password" class="form-label">Password</label>
              <input type="password" name="password" id="password" class="form-control" maxlength=12 required>
            </div>
             			<!-- CAPTCHA Section -->
                        <div class="mb-3">
                            <label for="captcha" class="form-label">CAPTCHA Verification</label>
                            <div class="d-flex align-items-center">
                                <div class="border p-2 bg-light text-center me-2" style="width: 120px; letter-spacing: 3px;">
                                    <?php echo $_SESSION['captcha']; ?>
                                </div>
                                <input type="text" name="captcha" id="captcha" class="form-control" placeholder="Enter the code" required>
                            </div>
                            <small class="text-muted">Enter the characters shown above</small>
                        </div>
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary" 
              onclick="if (<?php echo $userCount; ?> >= 1000000) { alert('Server busy! Please try again later.'); return false; }">
              Create Account
              </button>
            </div>
          </div>
        </form>
        <div class="d-grid gap-2 mt-3">
          <a href="./?page=login" class="btn btn-outline-primary">Already have an account? Log in</a>
        </div>
      </div>
    </div>
  </div>
</section>