<?php
    include "../conn.php";
    // session_start();
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        // If not logged in, redirect to the login page
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
            <h2 class="text-center bg-primary text-light h5 mb-5 py-2">General System Report</h2>
            <div class="row g-3">
                <div class="row mt-3">

                    <div class="col-lg-3">
                        <div class="card mb-3 shadow" style="height:80px">
                            <a href="?page=report_transactions" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                                <h5><span class="text-primary">Transactions Report</h5>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="card mb-3 shadow" style="height:80px">
                            <a href="?page=report_sales" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                                <h5><span class="text-primary">Sales Report</h5>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="card mb-3 shadow" style="height:80px">
                            <a href="?page=report_users" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                                <h5><span class="text-primary">Users Report</h5>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="card mb-3 shadow" style="height:80px">
                            <a href="?page=report_api" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                                <h5><span class="text-primary">APIs Report</h5>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="card mb-3 shadow" style="height:80px">
                            <a href="?page=report_messages" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                                <h5><span class="text-primary">Messages Report</h5>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="card mb-3 shadow" style="height:80px">
                            <a href="?page=report_services" class="card-body bg-light text-dark text-center text-decoration-none stretched-link">
                                <h5><span class="text-primary">Services Report</h5>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<section class="py-5">

</section>