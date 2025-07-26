<?php
 include "../conn.php";

    if (!isset($_SESSION['user_id'])) {
        // If not logged in, redirect to the login page
        header("Location: ./?page=login");
        exit;
    }

    //get transaction details from table 
    
?>
<div class="row">
  
  <div class="col-lg">
    <div class="container">
      <div class="col-lg-6 mx-auto">
        <div class="card mb-3">
          <div class="card-body">
            <h5 class="text-center">Transaction Receipt</h5>
            <h4 class="text-center text-success"><?php echo $status ?></h5>
            <p class="small text-center fst-italic"><small><strong>Reference ID:</strong> <?= $ref_id ?></small></p>
            <p class=""><strong>Date:</strong> <?= $created_at ?></p>
            <p class=""><strong>Description:</strong> <?= $product_description ?></p>
            <!-- <p class="">Customer Name: <?= $fullname ?></p> -->
            <p class=""><strong>Phone Number:</strong> <?= $phone_number ?></p>
            <p class=""><strong>Amount:</strong> &#8358;<?= number_format($amount, 2) ?></p>
            <div class="form-group mt-3 text-center">
                    <a href="?page=transactions" class="btn btn-primary btn-block mt-3">Close</a>
                </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>