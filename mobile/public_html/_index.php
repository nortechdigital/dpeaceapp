<?php
session_start();
include("./_/conn.php");
$pg = isset($_GET['page']) ? $_GET['page'] : 'home';

if (isset($_SESSION['user_id'])) {
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
}


$marquee_query = "SELECT content FROM news_flash ORDER BY created_at DESC LIMIT 1";
$marquee_stmt = $conn->prepare($marquee_query);
if ($marquee_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$marquee_stmt->execute();
$marquee_stmt->bind_result($marquee_content);
$marquee_stmt->fetch();
$marquee_stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="theme-color" content="#3367D6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <link rel="apple-touch-icon" href="https://dpeaceapp.com/img/icon-192.png">
  <title>DPeace App</title>
  <link rel="icon" type="image/png" href="./img/icon.png" /> 
  <link rel="manifest" href="https://dpeaceapp.com/manifest.json">
  <link rel="stylesheet" href="./css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
  
  <style>
    .navbar-brand img {
      width: 100px;
      height: auto;
    }
    .user-profile {
      display: flex;
      align-items: center;
    }
    .user-profile img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 10px;
    }
    .dropdown-menu {
      min-width: 200px;
    }
  </style>
  
<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/6838fcc318e960190e216e39/1isf9muda';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
    <script>
      if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
        location.href = 'https://' + location.hostname + location.pathname + location.search + location.hash;
      }
    </script>
</head>
<body>

<?php if (isset($_SESSION['user_id']) && $pg !== 'view_transaction' && $pg !== 'receipt'): ?>

  <?php
  $user_id = $_SESSION['user_id'];
  $query = "SELECT balance FROM wallets WHERE user_id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->bind_result($balance);
  $stmt->fetch();
  $stmt->close();
  
  $sql = "SELECT SUM(profit) AS bonus FROM transactions WHERE user_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->bind_result($bonus);
  $stmt->fetch();
  $stmt->close();
?>

  <nav class="navbar navbar-expand-lg bg-white">
    <div class="container">
      <a class="navbar-brand text-center" href="#">
        <img src="./img/dpeace-app.png" alt="DPeace" class="d-lg-block d-none" width="200" height="auto">
        <h6 class="mb-4 text-dark d-lg-block d-none">Better Life Begins with You!</h6>
      </a>
      <a class="navbar-brand text-center w-100 d-lg-none" href="#" style="display: flex; flex-direction: column; align-items: center;">
        <img src="./img/dpeace-app.png" alt="DPeace" class="" width="200" height="auto">
        <h6 class="mb-4 text-dark">Better Life Begins with You!</h6>
      </a>
      <?php if ($pg !== 'home'): ?>
      <h3 class="text-primary text-center flex-grow-1 w-100">Wallet Balance: <span class="h1">&#8358;<?php echo number_format($balance, 2);?></span></h3>
      <?php endif; ?>
      <div class="user-profile d-flex justify-content-center w-100">
        <img src="./img/image.png" alt="User Profile">
        <div class="dropdown">
          <a class="btn btn-light dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="h5">Welcome, <?php echo ucfirst(htmlspecialchars($_SESSION['firstname'], ENT_QUOTES, 'UTF-8')) ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="./?page=profile">View Profile</a></li>
            <li><a class="dropdown-item" href="./?page=kyc">KYC</a></li>
            <li><a class="dropdown-item" href="./?page=settings">Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="./_/ac_logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </div>
  </nav>
  <div class="fw-3 h5 text-primary"><marquee><?php echo htmlspecialchars($marquee_content, ENT_QUOTES, 'UTF-8'); ?></marquee></div>
  <?php elseif($pg != 'login' && $pg != 'signup' && $pg != 'admin_login' && $pg != 'forgot_password' && $pg != 'view_transaction' && $pg != 'receipt'): ?>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand text-center" href="./">
      <img src="./img/dpeace-app.png" alt="Site Logo" class="" style="width:75px;height:auto;">
      <h6 class="mb-4 text-dark">Better Life Begins with You!</h6>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target=".navbar-collapse">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item d-none">
          <a class="nav-link active" href="#">Pricing</a>
        </li>
        <li class="nav-item d-none">
       </li>
      </ul>
      
      <!-- Phone number link -->
      <a href="tel:+2349090909090" class="btn me-2 d-none">0909-090-9090</a>

      <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Logged in, show "Log out" button -->
        <a href="./_/ac_logout.php" class="btn btn-danger me-2">Log out</a>
      <?php else: ?>
        <!-- Not logged in, show "Log in" and "Sign Up" buttons -->
        <a href="?page=about" class="btn btn-outline-primary me-2">About us</a>
        <a href="?page=login" class="btn btn-primary me-2">Log in</a>
        <a href="?page=signup" class="btn btn-outline-primary">Sign Up</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<?php endif; ?>
<div>
  <?php
include "./pg/$pg.php";
?>
<?php if($pg != 'login' && $pg != 'signup'  && $pg != 'view_transaction' && $pg != 'receipt'): ?>
  <footer class="bg-secondary text-light">
    <div class="container text-center py-3">
    © 2025 DPeace App || All Rights Researved.
    </div>
  </footer>
<?php endif; ?>

  </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('https://dpeaceapp.com/sw.js')
      .then(registration => {
        console.log('ServiceWorker registration successful');
      })
      .catch(err => {
        console.log('ServiceWorker registration failed: ', err);
      });
  });
}
</script>
<script>
  // Set the idle time limit (in milliseconds)
  const idleTimeLimit = 3 * 60 * 1000; // 3 minutes

  let idleTimer;

  // Reset the idle timer on user activity
  function resetIdleTimer() {
    clearTimeout(idleTimer);
    idleTimer = setTimeout(logoutUser, idleTimeLimit);
  }

  // Log out the user
  function logoutUser() {
    window.location.href = './_/ac_logout.php';
  }

  // Listen for user activity events
  window.onload = resetIdleTimer;
  window.onmousemove = resetIdleTimer;
  window.onmousedown = resetIdleTimer; // catches touchscreen presses
  window.ontouchstart = resetIdleTimer;
  window.onclick = resetIdleTimer;     // catches touchpad clicks
  window.onkeypress = resetIdleTimer;
  window.addEventListener('scroll', resetIdleTimer, true); // improved; see comments

</script>

</body>
</html>