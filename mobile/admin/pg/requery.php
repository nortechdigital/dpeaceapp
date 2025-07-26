<div class="row">
    <div class="col-lg-2">
        <?php include "./inc/sidebar.php" ?>
    </div>
    <div class="col-lg-10">
        <div class="container py-2">
            <h2 class="text-center bg-primary text-light h5 mb-5 py-2">Requery Transaction</h2>
            <div class="card mb-3 shadow">
                <div class="card-body bg-light text-dark">
                    <form action="../_/ac_requery.php" method="post">
                        <div class="mb-3">
                            <label for="request_id" class="form-label">Request Id</label>
                            <input type="text" name="request_id" id="request_id" class="form-control mb-3" require>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                   </form>
                </div>
            </div>
        </div>
    </div>
</div>