<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('location: ../?page=login');
}
$pg = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
// print_r($_SESSION);die;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DPeace App</title>
  <link rel="icon" type="image/png" href="./img/icon.png" />
  <link rel="stylesheet" href="../css/bootstrap.min.css">
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

</head>
<body>


<nav class="navbar navbar-expand-lg bg-white">
    <div class="container">
    <a class="navbar-brand text-center" href="#">
        <img src="../img/dpeace-app.png" alt="DPeace App Logo" class="d-lg-block d-none" width="200" height="auto">
        <h6 class="mb-4 text-dark d-lg-block d-none">Better Life Begins with You!</h6>
      </a>
      <a class="navbar-brand text-center w-100 d-lg-none" href="#" style="display: flex; flex-direction: column; align-items: center;">
        <img src="../img/dpeace-app.png" alt="DPeace App Logo" class="" width="200" height="auto">
        <h6 class="mb-4 text-dark">Better Life Begins with You!</h6>
      </a>
      <div class="row">
        <div class=""></div>
      <div class="user-profile d-flex  w-100">
        <img src="../img/image.png" alt="User Profile">
        <div class="dropdown">
          <a class="btn btn-light dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="h5">Welcome, <?php echo ucfirst($_SESSION['firstname']) ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item"><?php echo htmlspecialchars($_SESSION['email'], ENT_QUOTES, 'UTF-8') ?></a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="../_/ac_logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
      </div>
  </nav>

<?php
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
switch ($page) {
    case 'view_user':
        include './pg/view_user.php';
        break;
    default:
        include "./pg/$pg.php";
        break;
}
?>

<footer class="bg-secondary text-light">
    <div class="container text-center py-3">
      © 2025 DPeace App || All Rights Researved.
    </div>
  </footer>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
 
<script>
  // Set the idle time limit (in milliseconds)
  const idleTimeLimit = 5 * 60 * 1000; // 5 minutes

  let idleTimer;

  // Reset the idle timer on user activity
  function resetIdleTimer() {
    clearTimeout(idleTimer);
    idleTimer = setTimeout(logoutUser, idleTimeLimit);
  }

  // Log out the user
  function logoutUser() {
    window.location.href = '../_/ac_logout.php';
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