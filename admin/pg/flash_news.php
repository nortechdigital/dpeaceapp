<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL); 
include "../conn.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'];
   
    $query = "UPDATE news_flash SET content = ? WHERE id = 1";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("s", $content);
    if ($stmt->execute()) {
      echo "<script>alert('News Flash update successful');window.location.href='./?page=flash_news';</script>";
            exit;
    } else {
        echo "<script>alert('News Flash update not successful');</script>";
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
      <h2 class="text-center bg-primary text-light h5">FLASH NEWS UPDATE </h2>
      <div class="row py-3">
        <div class="col-md-8 offset-md-2">
          <div class="card shadow p-4">
            <form method="POST">
              
              <fieldset>
                        
                <textarea name="content" id="content" class="form-control" rows="2" placeholder="Enter News Content" required></textarea>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Update News</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
