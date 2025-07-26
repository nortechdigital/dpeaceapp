<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    // Check if user exists
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", "$username", "$password");
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Generate token and expiry
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in database
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        $stmt->execute([$token, $expiry, $user['id']]);
        
        // Send email with reset link
        $resetLink = "https://dpeaceapp.com/?page=reset_password&token=$token";
        $message = "Click here to reset your password: $resetLink";
    	echo "<a href='$resetLink' target='_blank'>Click here to reset your password</a>";
        // mail($email, "Password Reset", $message);
		// Check if mail is configured properly
		if (mail($email, "Password Reset", $message)) {
    		// echo 'Mail function works';
		} else {
    		echo 'Mail function failed';
		}
    }
    
    // Always show success message to prevent email enumeration
    $echo = "If an account exists with that email, a reset link has been sent.";
}
?>
<section class="vh-100 d-flex align-items-center">
  <div class="container py-5">
    <div class="row justify-content-center">
      <!-- Left Side Image -->
      <div class="col-lg-6 d-none d-lg-block text-center">
        <img src="./img/dpeace-app.png" alt="Site Logo" class="img-fluid" style="max-width: 100%; height: auto;">
      </div>

      <!-- Login Form -->
      <div class="col-lg-5">
		<?php if($echo): ?>
		<div class="alert alert-warning alert-dismissible fade show" role="alert">
  			<strong></strong> <?= $echo ?>
  			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
        <?php endif; ?>
        <form action="" method="post" class="card shadow-lg">
          <div class="card-body p-4">
            <div class="mb-3 text-center">
              <h1 class="h3 fw-bold"><u>Forgot Password</u></h1>
            </div>

            <!-- Email Field -->
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" name="email" id="email" class="form-control" required>
            </div>

           
            <!-- Send otp Button -->
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Reset Password</button>
            </div>
          </div>
        </form>

        <!-- Login Page Links -->
        <div class="text-center mt-3">
          <a href="?page=login" class="btn btn-outline-secondary">Back to Login Page</a>
        </div>
      </div>
    </div>
  </div>
</section>