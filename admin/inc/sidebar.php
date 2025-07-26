<div class="container py-2 d-none d-md-block">
      <div class="d-grid">
        <a href="./?page=dashboard" class="btn btn-light text-primary mb-2">Dashboard</a>
        <a href="./?page=transactions" class="btn btn-light text-primary mb-2">Transactions</a>
        <a href="./?page=flash_news" class="btn btn-light text-primary mb-2">Flash News</a>
		<?php if($_SESSION['role']=='admin'):?> 
        <a href="./?page=users" class="btn btn-light text-primary mb-2">Users</a>
        <a href="./?page=sales_analysis" class="btn btn-light text-primary mb-2">Sales Analysis</a>
        <a href="./?page=summary" class="btn btn-light text-primary mb-2">Sales Summary</a>
        <a href="./?page=api_management" class="btn btn-light text-primary mb-2">API Management</a>
        <a href="./?page=services" class="btn btn-light text-primary mb-2">Services</a>
        <a href="./?page=wallet" class="btn btn-light text-primary mb-2">Wallet</a>
        <a href="./?page=providers" class="btn btn-light text-primary mb-2">Network Providers</a>
 		<?php endif; ?>
        <a href="./?page=messages" class="btn btn-light text-primary mb-2">Messages</a>
 		<?php if($_SESSION['role']=='admin'):?>
        <a href="./?page=reports" class="btn btn-light text-primary mb-2">Reports</a>
        <a href="./?page=settings" class="btn btn-light text-primary mb-2">Settings</a>
  		<?php endif; ?>
        <a href="./?page=logout" class="btn btn-light text-primary mb-2">Logout</a>
      </div>
    </div>

    <nav class="navbar navbar-expand-md navbar-light bg-light d-md-none">
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="sidebarMenu">
    <ul class="navbar-nav flex-column">
      <li class="nav-item">
        <a class="nav-link btn btn-light text-primary mb-2" href="./?page=dashboard">Dashboard</a>
      </li>
      <li class="nav-item">
        <a class="nav-link btn btn-light text-primary mb-2" href="./?page=transactions">Transactions</a>
      </li>
      <li class="nav-item">
        <a class="nav-link btn btn-light text-primary mb-2" href="./?page=flash_news">Flash News</a>
      </li>
      <?php if($_SESSION['role']=='admin'):?>
      <li class="nav-item">
        <a class="nav-link btn btn-light text-primary mb-2" href="./?page=users">Users</a>
      </li>
      <li class="nav-item">
        <a class="nav-link btn btn-light text-primary mb-2" href="./?page=sales_analysis">Sales Analysis</a>
      </li>
      <li class="nav-item">
        <a class="nav-link btn btn-light text-primary mb-2" href="./?page=summary">Sales Summary</a>
      </li>
      <li class="nav-item">
        <a class="nav-link btn btn-light text-primary mb-2" href="./?page=api_management">API Management</a>
      </li>
      <li class="nav-item">
        <a class="nav-link btn btn-light text-primary mb-2" href="./?page=services">Services</a>
      </li>
      <li class="nav-item">
        <a class="nav-link btn btn-light text-primary mb-2" href="./?page=wallet">Wallets</a>
      </li>
      <li class="nav-item">
        <a class="nav-link btn btn-light text-primary mb-2" href="./?page=providers">Network Providers</a>
      </li>
      <?php endif; ?>
      <li class="nav-item">
        <a class="nav-link btn btn-light text-primary mb-2" href="./?page=messages">Messages</a>
      </li>
      <?php if($_SESSION['role']=='admin'):?>
      <li class="nav-item">
        <a class="nav-link btn btn-light text-primary mb-2" href="./?page=reports">Reports</a>
      </li>
      <li class="nav-item">
        <a class="nav-link btn btn-light text-primary mb-2" href="./?page=settings">Settings</a>
      </li>
      <?php endif; ?>
      <li class="nav-item">
        <a class="nav-link btn btn-light text-primary mb-2" href="./?page=logout">Logout</a>
      </li>
    </ul>
  </div>
</nav>

<!-- Ensure Bootstrap JS and dependencies are included -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" defer></script>