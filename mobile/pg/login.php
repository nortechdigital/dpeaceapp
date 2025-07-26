<section class="vh-100 d-flex align-items-center">
  <div class="container py-5">
    <div class="row justify-content-center">
      <!-- Left Side Image -->
      <div class="col-lg-6 d-none d-lg-block ">
        <img src="./img/dpeace-app.png" alt="Site Logo" class="img-fluid" style="max-width: 90%; height: auto;"><br>
        <h2 class="mb-4 text-dark">Better Life Begins with You!</h3>
      </div>

      <!-- Login Form -->
      <div class="col-lg-5">
        <form action="./_/ac_login.php" method="post" class="card shadow-lg">
          <div class="card-body p-4">
            <div class="mb-3 text-center">
			  <img src="./img/dpeace-app.png" alt="Site Logo" class="img-fluid d-lg-none" style="max-width: 45%; height: auto;">
              <h1 class="h3 fw-bold"><u>User Login</u></h1>
            </div>

            <!-- Email Field -->
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" name="login" id="login" class="form-control" required>
            </div>

            <!-- Password Field -->
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <div class="input-group">
              <input type="password" name="password" id="password" class="form-control" required>
              <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                <i class="bi bi-eye"></i>
              </button>
              </div>
            </div>
            <script>
              document.getElementById('togglePassword').addEventListener('click', function () {
              const passwordField = document.getElementById('password');
              const passwordFieldType = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
              passwordField.setAttribute('type', passwordFieldType);
              this.innerHTML = passwordFieldType === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
              });
            </script>

            <!-- Forgot Password Link -->
            <div class="text-end mb-3">
              <a href="?page=forgot_password" class="text-decoration-none">Forgot Password?</a>
            </div>

            <!-- Sign In Button -->
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Sign in</button>
            </div>
          </div>
        </form>

        <!-- Signup and Home Links -->
        <div class="text-center mt-3">
          <a href="?page=signup" class="btn btn-secondary">Signup Now</a>
          
        </div>
      </div>
    </div>
  </div>
</section>
