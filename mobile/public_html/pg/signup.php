<section class="">
  <div class="container py-5">
    <div class="row">
    <div class="col-lg-6 d-none d-lg-block ">
        <img src="./img/dpeace-app.png" alt="Site Logo" class="img-fluid" style="max-width: 90%; height: auto;"><br>
        <h2 class="mb-4 text-dark">Better Life Begins with You!</h3>
      </div>
      <div class="col-lg">
        <form action="./_/ac_signup.php" method="post" class="card">
          <div class="card-body">
            <div class="mb-2 text-center">
              <h3>Sign Up</h3>
            </div>
            <hr>
            <div class="mb-2">
              <label for="firstName" class="form-label">Firstname</label>
              <input type="text" name="firstname" id="firstName" class="form-control" required>
            </div>
            <div class="mb-2">
              <label for="lastName" class="form-label">Lastname</label>
              <input type="text" name="lastname" id="lastName" class="form-control" required>
            </div>
            <div class="mb-2">
              <label for="email" class="form-label">Email</label>
              <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-2">
              <label for="phone" class="form-label">Phone</label>
              <input type="tel" name="phone" id="phone" class="form-control" required>
            </div>
            <div class="mb-2">
              <label for="password" class="form-label">Password</label>
              <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <!-- <div class="mb-2">
              <label for="address" class="form-label">Address</label>
              <input type="text" name="address" id="address" class="form-control" required>
            </div>
            <div class="mb-2">
              <label for="dob" class="form-label">Date of Birth</label>
              <input type="date" name="dob" id="dob" class="form-control" required>
            </div>
            <div class="mb-2">
              <label for="idNumber" class="form-label">NIN Number</label>
              <input type="text" name="id_number" id="idNumber" class="form-control" required>
            </div> -->
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary">Create Account</button>
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