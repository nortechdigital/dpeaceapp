<?php
include "../conn.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

// Fetch current settings from the database
$query = "SELECT * FROM settings WHERE id = 1"; 
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Assign fetched values to variables
    foreach ($row as $key => $value) {
        $$key = $value;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get data from the form
    $buyingPriceMTNData = isset($_POST['buyingPriceMTNData']) ? $_POST['buyingPriceMTNData'] : null;
    $sellingPriceMTNData = isset($_POST['sellingPriceMTNData']) ? $_POST['sellingPriceMTNData'] : null;
    $profitMTNData = isset($_POST['profitMTNData']) ? $_POST['profitMTNData'] : null;
    $subscriberDiscountMTNData = isset($_POST['subscriberDiscountMTNData']) ? $_POST['subscriberDiscountMTNData'] : null;
    $agentDiscountMTNData = isset($_POST['agentDiscountMTNData']) ? $_POST['agentDiscountMTNData'] : null;
    $vendorDiscountMTNData = isset($_POST['vendorDiscountMTNData']) ? $_POST['vendorDiscountMTNData'] : null;
    $buyingPriceMTNAirtime = isset($_POST['buyingPriceMTNAirtime']) ? $_POST['buyingPriceMTNAirtime'] : null;
    $sellingPriceMTNAirtime = isset($_POST['sellingPriceMTNAirtime']) ? $_POST['sellingPriceMTNAirtime'] : null;
    $profitMTNAirtime = isset($_POST['profitMTNAirtime']) ? $_POST['profitMTNAirtime'] : null;
    $subscriberDiscountMTNAirtime = isset($_POST['subscriberDiscountMTNAirtime']) ? $_POST['subscriberDiscountMTNAirtime'] : null;
    $agentDiscountMTNAirtime = isset($_POST['agentDiscountMTNAirtime']) ? $_POST['agentDiscountMTNAirtime'] : null;
    $vendorDiscountMTNAirtime = isset($_POST['vendorDiscountMTNAirtime']) ? $_POST['vendorDiscountMTNAirtime'] : null;

    $buyingPriceAirtelData = isset($_POST['buyingPriceAirtelData']) ? $_POST['buyingPriceAirtelData'] : null;
    $sellingPriceAirtelData = isset($_POST['sellingPriceAirtelData']) ? $_POST['sellingPriceAirtelData'] : null;
    $profitAirtelData = isset($_POST['profitAirtelData']) ? $_POST['profitAirtelData'] : null;
    $subscriberDiscountAirtelData = isset($_POST['subscriberDiscountAirtelData']) ? $_POST['subscriberDiscountAirtelData'] : null;
    $agentDiscountAirtelData = isset($_POST['agentDiscountAirtelData']) ? $_POST['agentDiscountAirtelData'] : null;
    $vendorDiscountAirtelData = isset($_POST['vendorDiscountAirtelData']) ? $_POST['vendorDiscountAirtelData'] : null;
    $buyingPriceAirtelAirtime = isset($_POST['buyingPriceAirtelAirtime']) ? $_POST['buyingPriceAirtelAirtime'] : null;
    $sellingPriceAirtelAirtime = isset($_POST['sellingPriceAirtelAirtime']) ? $_POST['sellingPriceAirtelAirtime'] : null;
    $profitAirtelAirtime = isset($_POST['profitAirtelAirtime']) ? $_POST['profitAirtelAirtime'] : null;
    $subscriberDiscountAirtelAirtime = isset($_POST['subscriberDiscountAirtelAirtime']) ? $_POST['subscriberDiscountAirtelAirtime'] : null;
    $agentDiscountAirtelAirtime = isset($_POST['agentDiscountAirtelAirtime']) ? $_POST['agentDiscountAirtelAirtime'] : null;
    $vendorDiscountAirtelAirtime = isset($_POST['vendorDiscountAirtelAirtime']) ? $_POST['vendorDiscountAirtelAirtime'] : null;

    $buyingPriceGloData = isset($_POST['buyingPriceGloData']) ? $_POST['buyingPriceGloData'] : null;
    $sellingPriceGloData = isset($_POST['sellingPriceGloData']) ? $_POST['sellingPriceGloData'] : null;
    $profitGloData = isset($_POST['profitGloData']) ? $_POST['profitGloData'] : null;
    $subscriberDiscountGloData = isset($_POST['subscriberDiscountGloData']) ? $_POST['subscriberDiscountGloData'] : null;
    $agentDiscountGloData = isset($_POST['agentDiscountGloData']) ? $_POST['agentDiscountGloData'] : null;
    $vendorDiscountGloData = isset($_POST['vendorDiscountGloData']) ? $_POST['vendorDiscountGloData'] : null;
    $buyingPriceGloAirtime = isset($_POST['buyingPriceGloAirtime']) ? $_POST['buyingPriceGloAirtime'] : null;
    $sellingPriceGloAirtime = isset($_POST['sellingPriceGloAirtime']) ? $_POST['sellingPriceGloAirtime'] : null;
    $profitGloAirtime = isset($_POST['profitGloAirtime']) ? $_POST['profitGloAirtime'] : null;
    $subscriberDiscountGloAirtime = isset($_POST['subscriberDiscountGloAirtime']) ? $_POST['subscriberDiscountGloAirtime'] : null;
    $agentDiscountGloAirtime = isset($_POST['agentDiscountGloAirtime']) ? $_POST['agentDiscountGloAirtime'] : null;
    $vendorDiscountGloAirtime = isset($_POST['vendorDiscountGloAirtime']) ? $_POST['vendorDiscountGloAirtime'] : null;

    $buyingPrice9mobileData = isset($_POST['buyingPrice9mobileData']) ? $_POST['buyingPrice9mobileData'] : null;
    $sellingPrice9mobileData = isset($_POST['sellingPrice9mobileData']) ? $_POST['sellingPrice9mobileData'] : null;
    $profit9mobileData = isset($_POST['profit9mobileData']) ? $_POST['profit9mobileData'] : null;
    $subscriberDiscount9mobileData = isset($_POST['subscriberDiscount9mobileData']) ? $_POST['subscriberDiscount9mobileData'] : null;
    $agentDiscount9mobileData = isset($_POST['agentDiscount9mobileData']) ? $_POST['agentDiscount9mobileData'] : null;
    $vendorDiscount9mobileData = isset($_POST['vendorDiscount9mobileData']) ? $_POST['vendorDiscount9mobileData'] : null;
    $buyingPrice9mobileAirtime = isset($_POST['buyingPrice9mobileAirtime']) ? $_POST['buyingPrice9mobileAirtime'] : null;
    $sellingPrice9mobileAirtime = isset($_POST['sellingPrice9mobileAirtime']) ? $_POST['sellingPrice9mobileAirtime'] : null;
    $profit9mobileAirtime = isset($_POST['profit9mobileAirtime']) ? $_POST['profit9mobileAirtime'] : null;
    $subscriberDiscount9mobileAirtime = isset($_POST['subscriberDiscount9mobileAirtime']) ? $_POST['subscriberDiscount9mobileAirtime'] : null;
    $agentDiscount9mobileAirtime = isset($_POST['agentDiscount9mobileAirtime']) ? $_POST['agentDiscount9mobileAirtime'] : null;
    $vendorDiscount9mobileAirtime = isset($_POST['vendorDiscount9mobileAirtime']) ? $_POST['vendorDiscount9mobileAirtime'] : null;

    $buyingPriceSmileData = isset($_POST['buyingPriceSmileData']) ? $_POST['buyingPriceSmileData'] : null;
    $sellingPriceSmileData = isset($_POST['sellingPriceSmileData']) ? $_POST['sellingPriceSmileData'] : null;
    $profitSmileData = isset($_POST['profitSmileData']) ? $_POST['profitSmileData'] : null;
    $subscriberDiscountSmileData = isset($_POST['subscriberDiscountSmileData']) ? $_POST['subscriberDiscountSmileData'] : null;
    $agentDiscountSmileData = isset($_POST['agentDiscountSmileData']) ? $_POST['agentDiscountSmileData'] : null;
    $vendorDiscountSmileData = isset($_POST['vendorDiscountSmileData']) ? $_POST['vendorDiscountSmileData'] : null;

    $buyingPriceSwiftData = isset($_POST['buyingPriceSwiftData']) ? $_POST['buyingPriceSwiftData'] : null;
    $sellingPriceSwiftData = isset($_POST['sellingPriceSwiftData']) ? $_POST['sellingPriceSwiftData'] : null;
    $profitSwiftData = isset($_POST['profitSwiftData']) ? $_POST['profitSwiftData'] : null;
    $subscriberDiscountSwiftData = isset($_POST['subscriberDiscountSwiftData']) ? $_POST['subscriberDiscountSwiftData'] : null;
    $agentDiscountSwiftData = isset($_POST['agentDiscountSwiftData']) ? $_POST['agentDiscountSwiftData'] : null;
    $vendorDiscountSwiftData = isset($_POST['vendorDiscountSwiftData']) ? $_POST['vendorDiscountSwiftData'] : null;

    $buyingPriceSpectranetData = isset($_POST['buyingPriceSpectranetData']) ? $_POST['buyingPriceSpectranetData'] : null;
    $sellingPriceSpectranetData = isset($_POST['sellingPriceSpectranetData']) ? $_POST['sellingPriceSpectranetData'] : null;
    $profitSpectranetData = isset($_POST['profitSpectranetData']) ? $_POST['profitSpectranetData'] : null;
    $subscriberDiscountSpectranetData = isset($_POST['subscriberDiscountSpectranetData']) ? $_POST['subscriberDiscountSpectranetData'] : null;
    $agentDiscountSpectranetData = isset($_POST['agentDiscountSpectranetData']) ? $_POST['agentDiscountSpectranetData'] : null;
    $vendorDiscountSpectranetData = isset($_POST['vendorDiscountSpectranetData']) ? $_POST['vendorDiscountSpectranetData'] : null;

    // Prepare update query
    $query = "UPDATE settings SET 
        buyingPriceMTNData = IF(?, ?, buyingPriceMTNData),
        sellingPriceMTNData = IF(?, ?, sellingPriceMTNData),
        profitMTNData = IF(?, ?, profitMTNData),
        subscriberDiscountMTNData = IF(?, ?, subscriberDiscountMTNData),
        agentDiscountMTNData = IF(?, ?, agentDiscountMTNData),
        vendorDiscountMTNData = IF(?, ?, vendorDiscountMTNData),
        buyingPriceMTNAirtime = IF(?, ?, buyingPriceMTNAirtime),
        sellingPriceMTNAirtime = IF(?, ?, sellingPriceMTNAirtime),
        profitMTNAirtime = IF(?, ?, profitMTNAirtime),
        subscriberDiscountMTNAirtime = IF(?, ?, subscriberDiscountMTNAirtime),
        agentDiscountMTNAirtime = IF(?, ?, agentDiscountMTNAirtime),
        vendorDiscountMTNAirtime = IF(?, ?, vendorDiscountMTNAirtime),
        buyingPriceAirtelData = IF(?, ?, buyingPriceAirtelData),
        sellingPriceAirtelData = IF(?, ?, sellingPriceAirtelData),
        profitAirtelData = IF(?, ?, profitAirtelData),
        subscriberDiscountAirtelData = IF(?, ?, subscriberDiscountAirtelData),
        agentDiscountAirtelData = IF(?, ?, agentDiscountAirtelData),
        vendorDiscountAirtelData = IF(?, ?, vendorDiscountAirtelData),
        buyingPriceAirtelAirtime = IF(?, ?, buyingPriceAirtelAirtime),
        sellingPriceAirtelAirtime = IF(?, ?, sellingPriceAirtelAirtime),
        profitAirtelAirtime = IF(?, ?, profitAirtelAirtime),
        subscriberDiscountAirtelAirtime = IF(?, ?, subscriberDiscountAirtelAirtime),
        agentDiscountAirtelAirtime = IF(?, ?, agentDiscountAirtelAirtime),
        vendorDiscountAirtelAirtime = IF(?, ?, vendorDiscountAirtelAirtime),
        buyingPriceGloData = IF(?, ?, buyingPriceGloData),
        sellingPriceGloData = IF(?, ?, sellingPriceGloData),
        profitGloData = IF(?, ?, profitGloData),
        subscriberDiscountGloData = IF(?, ?, subscriberDiscountGloData),
        agentDiscountGloData = IF(?, ?, agentDiscountGloData),
        vendorDiscountGloData = IF(?, ?, vendorDiscountGloData),
        buyingPriceGloAirtime = IF(?, ?, buyingPriceGloAirtime),
        sellingPriceGloAirtime = IF(?, ?, sellingPriceGloAirtime),
        profitGloAirtime = IF(?, ?, profitGloAirtime),
        subscriberDiscountGloAirtime = IF(?, ?, subscriberDiscountGloAirtime),
        agentDiscountGloAirtime = IF(?, ?, agentDiscountGloAirtime),
        vendorDiscountGloAirtime = IF(?, ?, vendorDiscountGloAirtime),
        buyingPrice9mobileData = IF(?, ?, buyingPrice9mobileData),
        sellingPrice9mobileData = IF(?, ?, sellingPrice9mobileData),
        profit9mobileData = IF(?, ?, profit9mobileData),
        subscriberDiscount9mobileData = IF(?, ?, subscriberDiscount9mobileData),
        agentDiscount9mobileData = IF(?, ?, agentDiscount9mobileData),
        vendorDiscount9mobileData = IF(?, ?, vendorDiscount9mobileData),
        buyingPrice9mobileAirtime = IF(?, ?, buyingPrice9mobileAirtime),
        sellingPrice9mobileAirtime = IF(?, ?, sellingPrice9mobileAirtime),
        profit9mobileAirtime = IF(?, ?, profit9mobileAirtime),
        subscriberDiscount9mobileAirtime = IF(?, ?, subscriberDiscount9mobileAirtime),
        agentDiscount9mobileAirtime = IF(?, ?, agentDiscount9mobileAirtime),
        vendorDiscount9mobileAirtime = IF(?, ?, vendorDiscount9mobileAirtime),
        buyingPriceSmileData = IF(?, ?, buyingPriceSmileData),
        sellingPriceSmileData = IF(?, ?, sellingPriceSmileData),
        profitSmileData = IF(?, ?, profitSmileData),
        subscriberDiscountSmileData = IF(?, ?, subscriberDiscountSmileData),
        agentDiscountSmileData = IF(?, ?, agentDiscountSmileData),
        vendorDiscountSmileData = IF(?, ?, vendorDiscountSmileData),
        buyingPriceSwiftData = IF(?, ?, buyingPriceSwiftData),
        sellingPriceSwiftData = IF(?, ?, sellingPriceSwiftData),
        profitSwiftData = IF(?, ?, profitSwiftData),
        subscriberDiscountSwiftData = IF(?, ?, subscriberDiscountSwiftData),
        agentDiscountSwiftData = IF(?, ?, agentDiscountSwiftData),
        vendorDiscountSwiftData = IF(?, ?, vendorDiscountSwiftData),
        buyingPriceSpectranetData = IF(?, ?, buyingPriceSpectranetData),
        sellingPriceSpectranetData = IF(?, ?, sellingPriceSpectranetData),
        profitSpectranetData = IF(?, ?, profitSpectranetData),
        subscriberDiscountSpectranetData = IF(?, ?, subscriberDiscountSpectranetData),
        agentDiscountSpectranetData = IF(?, ?, agentDiscountSpectranetData),
        vendorDiscountSpectranetData = IF(?, ?, vendorDiscountSpectranetData)
        WHERE id = 1";

    // Prepare the statement
    $stmt = $conn->prepare($query);

    // Create an array of parameters
    $params = [
        $buyingPriceMTNData, $sellingPriceMTNData, $profitMTNData, $subscriberDiscountMTNData, 
        $agentDiscountMTNData, $vendorDiscountMTNData, $buyingPriceMTNAirtime, $sellingPriceMTNAirtime, 
        $profitMTNAirtime, $subscriberDiscountMTNAirtime, $agentDiscountMTNAirtime, $vendorDiscountMTNAirtime,
        $buyingPriceAirtelData, $sellingPriceAirtelData, $profitAirtelData, $subscriberDiscountAirtelData, 
        $agentDiscountAirtelData, $vendorDiscountAirtelData, $buyingPriceAirtelAirtime, $sellingPriceAirtelAirtime, 
        $profitAirtelAirtime, $subscriberDiscountAirtelAirtime, $agentDiscountAirtelAirtime, $vendorDiscountAirtelAirtime,
        $buyingPriceGloData, $sellingPriceGloData, $profitGloData, $subscriberDiscountGloData, 
        $agentDiscountGloData, $vendorDiscountGloData, $buyingPriceGloAirtime, $sellingPriceGloAirtime, 
        $profitGloAirtime, $subscriberDiscountGloAirtime, $agentDiscountGloAirtime, $vendorDiscountGloAirtime,
        $buyingPrice9mobileData, $sellingPrice9mobileData, $profit9mobileData, $subscriberDiscount9mobileData, 
        $agentDiscount9mobileData, $vendorDiscount9mobileData, $buyingPrice9mobileAirtime, $sellingPrice9mobileAirtime, 
        $profit9mobileAirtime, $subscriberDiscount9mobileAirtime, $agentDiscount9mobileAirtime, $vendorDiscount9mobileAirtime,
        $buyingPriceSmileData, $sellingPriceSmileData, $profitSmileData, $subscriberDiscountSmileData, 
        $agentDiscountSmileData, $vendorDiscountSmileData, 
        $buyingPriceSwiftData, $sellingPriceSwiftData, $profitSwiftData, $subscriberDiscountSwiftData, 
        $agentDiscountSwiftData, $vendorDiscountSwiftData, 
        $buyingPriceSpectranetData, $sellingPriceSpectranetData, $profitSpectranetData, $subscriberDiscountSpectranetData, 
        $agentDiscountSpectranetData, $vendorDiscountSpectranetData
    ];

    // Print count of params and number of placeholders in the query
// echo 'Number of params: ' . count($params) . "<br>";
// echo 'Number of placeholders: ' . substr_count($query, '?') . "<br>";


    // Create a string of types for bind_param
    $types = str_repeat('d', count($params));
    // echo 'Type string: ' . $types . "<br>";
    // Bind parameters dynamically
    $stmt->bind_param($types, ...$params);
    // echo 'Params: ' . implode(', ', $params) . "<br>";

    // Execute the query
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Settings updated successfully.');</script>";
    } else {
        echo "<script>alert('No changes were made.');</script>";
    }

    // Close statement
    $stmt->close();
}

$conn->close();
?>


<div class="row">
    <div class="col-lg-2">
        <?php include "./inc/sidebar.php" ?>
    </div>
    <div class="col-lg-10">
        <div class="container py-2">
            <h2 class="text-center bg-primary text-light h5 mb-5 py-2">System Settings</h2>
            <div class="accordion" id="settingsAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingMTN">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMTN" aria-expanded="true" aria-controls="collapseMTN">
                            <img src="../img/logo/mtn_logo.png" alt="MTN " style="height: 20px; margin-right: 10px;"> MTN
                        </button>
                    </h2>
                    <div id="collapseMTN" class="accordion-collapse collapse show" aria-labelledby="headingMTN" data-bs-parent="#settingsAccordion">
                        <div class="accordion-body">
                            <form method="POST"> 
                                <div class="row mt-3">
                                    <h5>Data Purchase</h5>
                                    <div class="col-lg">
                                        <label for="buyingPriceMTNData">Buying Price &#8358;</label>
                                        <input type="text" id="buyingPriceMTNData" name="buyingPriceMTNData" class="form-control" value="<?php echo htmlspecialchars($buyingPriceMTNData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="sellingPriceMTNData">Selling Price &#8358;</label>
                                        <input type="text" id="sellingPriceMTNData" name="sellingPriceMTNData" class="form-control" value="<?php echo htmlspecialchars($sellingPriceMTNData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="profitMTNData">Profit &#8358;</label>
                                        <input type="text" id="profitMTNData" name="profitMTNData" class="form-control" value="<?php echo htmlspecialchars($profitMTNData); ?>">
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg">Discount (%)</div>
                                    <div class="col-lg">
                                        <label for="subscriberDiscountMTNData">Subscriber</label>
                                        <input type="text" id="subscriberDiscountMTNData" name="subscriberDiscountMTNData" class="form-control" value="<?php echo htmlspecialchars($subscriberDiscountMTNData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="agentDiscountMTNData">Agent</label>
                                        <input type="text" id="agentDiscountMTNData" name="agentDiscountMTNData" class="form-control" value="<?php echo htmlspecialchars($agentDiscountMTNData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="vendorDiscountMTNData">Vendor</label>
                                        <input type="text" id="vendorDiscountMTNData" name="vendorDiscountMTNData" class="form-control" value="<?php echo htmlspecialchars($vendorDiscountMTNData); ?>">
                                    </div>
                                </div>
                                <hr>
                                <div class="row mt-3">
                                    <h5>Airtime Purchase</h5>
                                    <div class="col-lg">
                                        <label for="buyingPriceMTNAirtime">Buying Price &#8358;</label>
                                        <input type="text" id="buyingPriceMTNAirtime" name="buyingPriceMTNAirtime" class="form-control" value="<?php echo htmlspecialchars($buyingPriceMTNAirtime); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="sellingPriceMTNAirtime">Selling Price &#8358;</label>
                                        <input type="text" id="sellingPriceMTNAirtime" name="sellingPriceMTNAirtime" class="form-control" value="<?php echo htmlspecialchars($sellingPriceMTNAirtime); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="profitMTNAirtime">Profit &#8358;</label>
                                        <input type="text" id="profitMTNAirtime" name="profitMTNAirtime" class="form-control" value="<?php echo htmlspecialchars($profitMTNAirtime); ?>">
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg">Discount (%)</div>
                                    <div class="col-lg">
                                        <label for="subscriberDiscountMTNAirtime">Subscriber</label>
                                        <input type="text" id="subscriberDiscountMTNAirtime" name="subscriberDiscountMTNAirtime" class="form-control" value="<?php echo htmlspecialchars($subscriberDiscountMTNAirtime); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="agentDiscountMTNAirtime">Agent</label>
                                        <input type="text" id="agentDiscountMTNAirtime" name="agentDiscountMTNAirtime" class="form-control" value="<?php echo htmlspecialchars($agentDiscountMTNAirtime); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="vendorDiscountMTNAirtime">Vendor</label>
                                        <input type="text" id="vendorDiscountMTNAirtime" name="vendorDiscountMTNAirtime" class="form-control" value="<?php echo htmlspecialchars($vendorDiscountMTNAirtime); ?>">
                                    </div>
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary">Save Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingAirtel">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAirtel" aria-expanded="false" aria-controls="collapseAirtel">
                            <img src="../img/logo/airtel_logo.png" alt="Airtel" style="height: 20px; margin-right: 10px;"> Airtel
                        </button>
                    </h2>
                    <div id="collapseAirtel" class="accordion-collapse collapse" aria-labelledby="headingAirtel" data-bs-parent="#settingsAccordion">
                        <div class="accordion-body">
                            <form method="POST"> 
                                <div class="row mt-3">
                                    <h5>Data Purchase</h5>
                                    <div class="col-lg">
                                        <label for="buyingPriceAirtelData">Buying Price &#8358;</label>
                                        <input type="text" id="buyingPriceAirtelData" name="buyingPriceAirtelData" class="form-control" value="<?php echo htmlspecialchars($buyingPriceAirtelData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="sellingPriceAirtelData">Selling Price &#8358;</label>
                                        <input type="text" id="sellingPriceAirtelData" name="sellingPriceAirtelData" class="form-control" value="<?php echo htmlspecialchars($sellingPriceAirtelData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="profitAirtelData">Profit &#8358;</label>
                                        <input type="text" id="profitAirtelData" name="profitAirtelData" class="form-control" value="<?php echo htmlspecialchars($profitAirtelData); ?>">
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg">Discount (%)</div>
                                    <div class="col-lg">
                                        <label for="subscriberDiscountAirtelData">Subscriber</label>
                                        <input type="text" id="subscriberDiscountAirtelData" name="subscriberDiscountAirtelData" class="form-control" value="<?php echo htmlspecialchars($subscriberDiscountAirtelData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="agentDiscountAirtelData">Agent</label>
                                        <input type="text" id="agentDiscountAirtelData" name="agentDiscountAirtelData" class="form-control" value="<?php echo htmlspecialchars($agentDiscountAirtelData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="vendorDiscountAirtelData">Vendor</label>
                                        <input type="text" id="vendorDiscountAirtelData" name="vendorDiscountAirtelData" class="form-control" value="<?php echo htmlspecialchars($vendorDiscountAirtelData); ?>">
                                    </div>
                                </div>
                                <hr>
                                <div class="row mt-3">
                                    <h5>Airtime Purchase</h5>
                                    <div class="col-lg">
                                        <label for="buyingPriceAirtelAirtime">Buying Price &#8358;</label>
                                        <input type="text" id="buyingPriceAirtelAirtime" name="buyingPriceAirtelAirtime" class="form-control" value="<?php echo htmlspecialchars($buyingPriceAirtelAirtime); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="sellingPriceAirtelAirtime">Selling Price &#8358;</label>
                                        <input type="text" id="sellingPriceAirtelAirtime" name="sellingPriceAirtelAirtime" class="form-control" value="<?php echo htmlspecialchars($sellingPriceAirtelAirtime); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="profitAirtelAirtime">Profit &#8358;</label>
                                        <input type="text" id="profitAirtelAirtime" name="profitAirtelAirtime" class="form-control" value="<?php echo htmlspecialchars($profitAirtelAirtime); ?>">
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg">Discount (%)</div>
                                    <div class="col-lg">
                                        <label for="subscriberDiscountAirtelAirtime">Subscriber</label>
                                        <input type="text" id="subscriberDiscountAirtelAirtime" name="subscriberDiscountAirtelAirtime" class="form-control" value="<?php echo htmlspecialchars($subscriberDiscountAirtelAirtime); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="agentDiscountAirtelAirtime">Agent</label>
                                        <input type="text" id="agentDiscountAirtelAirtime" name="agentDiscountAirtelAirtime" class="form-control" value="<?php echo htmlspecialchars($agentDiscountAirtelAirtime); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="vendorDiscountAirtelAirtime">Vendor</label>
                                        <input type="text" id="vendorDiscountAirtelAirtime" name="vendorDiscountAirtelAirtime" class="form-control" value="<?php echo htmlspecialchars($vendorDiscountAirtelAirtime); ?>">
                                    </div>
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary">Save Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingGlo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGlo" aria-expanded="false" aria-controls="collapseGlo">
                            <img src="../img/logo/glo_logo.jpg" alt="Glo" style="height: 20px; margin-right: 10px;"> Glo
                        </button>
                    </h2>
                    <div id="collapseGlo" class="accordion-collapse collapse" aria-labelledby="headingGlo" data-bs-parent="#settingsAccordion">
                        <div class="accordion-body">
                            <form method="POST"> 
                                <div class="row mt-3">
                                    <h5>Data Purchase</h5>
                                    <div class="col-lg">
                                        <label for="buyingPriceGloData">Buying Price &#8358;</label>
                                        <input type="text" id="buyingPriceGloData" name="buyingPriceGloData" class="form-control" value="<?php echo htmlspecialchars($buyingPriceGloData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="sellingPriceGloData">Selling Price &#8358;</label>
                                        <input type="text" id="sellingPriceGloData" name="sellingPriceGloData" class="form-control" value="<?php echo htmlspecialchars($sellingPriceGloData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="profitGloData">Profit &#8358;</label>
                                        <input type="text" id="profitGloData" name="profitGloData" class="form-control" value="<?php echo htmlspecialchars($profitGloData); ?>">
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg">Discount (%)</div>
                                    <div class="col-lg">
                                        <label for="subscriberDiscountGloData">Subscriber</label>
                                        <input type="text" id="subscriberDiscountGloData" name="subscriberDiscountGloData" class="form-control" value="<?php echo htmlspecialchars($subscriberDiscountGloData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="agentDiscountGloData">Agent</label>
                                        <input type="text" id="agentDiscountGloData" name="agentDiscountGloData" class="form-control" value="<?php echo htmlspecialchars($agentDiscountGloData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="vendorDiscountGloData">Vendor</label>
                                        <input type="text" id="vendorDiscountGloData" name="vendorDiscountGloData" class="form-control" value="<?php echo htmlspecialchars($vendorDiscountGloData); ?>">
                                    </div>
                                </div>
                                <hr>
                                <div class="row mt-3">
                                    <h5>Airtime Purchase</h5>
                                    <div class="col-lg">
                                        <label for="buyingPriceGloAirtime">Buying Price &#8358;</label>
                                        <input type="text" id="buyingPriceGloAirtime" name="buyingPriceGloAirtime" class="form-control" value="<?php echo htmlspecialchars($buyingPriceGloAirtime); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="sellingPriceGloAirtime">Selling Price &#8358;</label>
                                        <input type="text" id="sellingPriceGloAirtime" name="sellingPriceGloAirtime" class="form-control" value="<?php echo htmlspecialchars($sellingPriceGloAirtime); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="profitGloAirtime">Profit &#8358;</label>
                                        <input type="text" id="profitGloAirtime" name="profitGloAirtime" class="form-control" value="<?php echo htmlspecialchars($profitGloAirtime); ?>">
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg">Discount (%)</div>
                                    <div class="col-lg">
                                        <label for="subscriberDiscountGloAirtime">Subscriber</label>
                                        <input type="text" id="subscriberDiscountGloAirtime" name="subscriberDiscountGloAirtime" class="form-control" value="<?php echo htmlspecialchars($subscriberDiscountGloAirtime); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="agentDiscountGloAirtime">Agent</label>
                                        <input type="text" id="agentDiscountGloAirtime" name="agentDiscountGloAirtime" class="form-control" value="<?php echo htmlspecialchars($agentDiscountGloAirtime); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="vendorDiscountGloAirtime">Vendor</label>
                                        <input type="text" id="vendorDiscountGloAirtime" name="vendorDiscountGloAirtime" class="form-control" value="<?php echo htmlspecialchars($vendorDiscountGloAirtime); ?>">
                                    </div>
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary">Save Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading9mobile">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse9mobile" aria-expanded="false" aria-controls="collapse9mobile">
                            <img src="../img/logo/9moble_logo.png" alt="9mobile" style="height: 20px; margin-right: 10px;"> 9mobile
                        </button>
                    </h2>
                    <div id="collapse9mobile" class="accordion-collapse collapse" aria-labelledby="heading9mobile" data-bs-parent="#settingsAccordion">
                        <div class="accordion-body">
                            <form method="POST"> 
                                <div class="row mt-3">
                                    <h5>Data Purchase</h5>
                                    <div class="col-lg">
                                        <label for="buyingPrice9mobileData">Buying Price &#8358;</label>
                                        <input type="text" id="buyingPrice9mobileData" name="buyingPrice9mobileData" class="form-control" value="<?php echo htmlspecialchars($buyingPrice9mobileData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="sellingPrice9mobileData">Selling Price &#8358;</label>
                                        <input type="text" id="sellingPrice9mobileData" name="sellingPrice9mobileData" class="form-control" value="<?php echo htmlspecialchars($sellingPrice9mobileData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="profit9mobileData">Profit &#8358;</label>
                                        <input type="text" id="profit9mobileData" name="profit9mobileData" class="form-control" value="<?php echo htmlspecialchars($profit9mobileData); ?>">
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg">Discount (%)</div>
                                    <div class="col-lg">
                                        <label for="subscriberDiscount9mobileData">Subscriber</label>
                                        <input type="text" id="subscriberDiscount9mobileData" name="subscriberDiscount9mobileData" class="form-control" value="<?php echo htmlspecialchars($subscriberDiscount9mobileData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="agentDiscount9mobileData">Agent</label>
                                        <input type="text" id="agentDiscount9mobileData" name="agentDiscount9mobileData" class="form-control" value="<?php echo htmlspecialchars($agentDiscount9mobileData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="vendorDiscount9mobileData">Vendor</label>
                                        <input type="text" id="vendorDiscount9mobileData" name="vendorDiscount9mobileData" class="form-control" value="<?php echo htmlspecialchars($vendorDiscount9mobileData); ?>">
                                    </div>
                                </div>
                                <hr>
                                <div class="row mt-3">
                                    <h5>Airtime Purchase</h5>
                                    <div class="col-lg">
                                        <label for="buyingPrice9mobileAirtime">Buying Price &#8358;</label>
                                        <input type="text" id="buyingPrice9mobileAirtime" name="buyingPrice9mobileAirtime" class="form-control" value="<?php echo htmlspecialchars($buyingPrice9mobileAirtime); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="sellingPrice9mobileAirtime">Selling Price &#8358;</label>
                                        <input type="text" id="sellingPrice9mobileAirtime" name="sellingPrice9mobileAirtime" class="form-control" value="<?php echo htmlspecialchars($sellingPrice9mobileAirtime); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="profit9mobileAirtime">Profit &#8358;</label>
                                        <input type="text" id="profit9mobileAirtime" name="profit9mobileAirtime" class="form-control" value="<?php echo htmlspecialchars($profit9mobileAirtime); ?>">
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg">Discount (%)</div>
                                    <div class="col-lg">
                                        <label for="subscriberDiscount9mobileAirtime">Subscriber</label>
                                        <input type="text" id="subscriberDiscount9mobileAirtime" name="subscriberDiscount9mobileAirtime" class="form-control" value="<?php echo htmlspecialchars($subscriberDiscount9mobileAirtime); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="agentDiscount9mobileAirtime">Agent</label>
                                        <input type="text" id="agentDiscount9mobileAirtime" name="agentDiscount9mobileAirtime" class="form-control" value="<?php echo htmlspecialchars($agentDiscount9mobileAirtime); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="vendorDiscount9mobileAirtime">Vendor</label>
                                        <input type="text" id="vendorDiscount9mobileAirtime" name="vendorDiscount9mobileAirtime" class="form-control" value="<?php echo htmlspecialchars($vendorDiscount9mobileAirtime); ?>">
                                    </div>
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary">Save Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingSmile">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSmile" aria-expanded="false" aria-controls="collapseSmile">
                            <img src="../img/logo/smile_logo.jpg" alt="Smile" style="height: 20px; margin-right: 10px;"> Smile
                        </button>
                    </h2>
                    <div id="collapseSmile" class="accordion-collapse collapse" aria-labelledby="headingSmile" data-bs-parent="#settingsAccordion">
                        <div class="accordion-body">
                            <form method="POST"> 
                                <div class="row mt-3">
                                    <h5>Smile Bundle</h5>
                                    <div class="col-lg">
                                        <label for="buyingPriceSmileData">Buying Price &#8358;</label>
                                        <input type="text" id="buyingPriceSmileData" name="buyingPriceSmileData" class="form-control" value="<?php echo htmlspecialchars($buyingPriceSmileData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="sellingPriceSmileData">Selling Price &#8358;</label>
                                        <input type="text" id="sellingPriceSmileData" name="sellingPriceSmileData" class="form-control" value="<?php echo htmlspecialchars($sellingPriceSmileData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="profitSmileData">Profit &#8358;</label>
                                        <input type="text" id="profitSmileData" name="profitSmileData" class="form-control" value="<?php echo htmlspecialchars($profitSmileData); ?>">
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg">Discount (%)</div>
                                    <div class="col-lg">
                                        <label for="subscriberDiscountSmileData">Subscriber</label>
                                        <input type="text" id="subscriberDiscountSmileData" name="subscriberDiscountSmileData" class="form-control" value="<?php echo htmlspecialchars($subscriberDiscountSmileData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="agentDiscountSmileData">Agent</label>
                                        <input type="text" id="agentDiscountSmileData" name="agentDiscountSmileData" class="form-control" value="<?php echo htmlspecialchars($agentDiscountSmileData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="vendorDiscountSmileData">Vendor</label>
                                        <input type="text" id="vendorDiscountSmileData" name="vendorDiscountSmileData" class="form-control" value="<?php echo htmlspecialchars($vendorDiscountSmileData); ?>">
                                    </div>
                                </div>
                                <hr>
                                
                                <div class="d-grid gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary">Save Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingSwift">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSwift" aria-expanded="false" aria-controls="collapseSwift">
                            <img src="../img/logo/swift_logo.jpg" alt="Swift" style="height: 20px; margin-right: 10px;"> Swift
                        </button>
                    </h2>
                    <div id="collapseSwift" class="accordion-collapse collapse" aria-labelledby="headingSwift" data-bs-parent="#settingsAccordion">
                        <div class="accordion-body">
                            <form method="POST"> 
                                <div class="row mt-3">
                                    <h5>Swift Bundle</h5>
                                    <div class="col-lg">
                                        <label for="buyingPriceSwiftData">Buying Price &#8358;</label>
                                        <input type="text" id="buyingPriceSwiftData" name="buyingPriceSwiftData" class="form-control" value="<?php echo htmlspecialchars($buyingPriceSwiftData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="sellingPriceSwiftData">Selling Price &#8358;</label>
                                        <input type="text" id="sellingPriceSwiftData" name="sellingPriceSwiftData" class="form-control" value="<?php echo htmlspecialchars($sellingPriceSwiftData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="profitSwiftData">Profit &#8358;</label>
                                        <input type="text" id="profitSwiftData" name="profitSwiftData" class="form-control" value="<?php echo htmlspecialchars($profitSwiftData); ?>">
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg">Discount (%)</div>
                                    <div class="col-lg">
                                        <label for="subscriberDiscountSwiftData">Subscriber</label>
                                        <input type="text" id="subscriberDiscountSwiftData" name="subscriberDiscountSwiftData" class="form-control" value="<?php echo htmlspecialchars($subscriberDiscountSwiftData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="agentDiscountSwiftData">Agent</label>
                                        <input type="text" id="agentDiscountSwiftData" name="agentDiscountSwiftData" class="form-control" value="<?php echo htmlspecialchars($agentDiscountSwiftData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="vendorDiscountSwiftData">Vendor</label>
                                        <input type="text" id="vendorDiscountSwiftData" name="vendorDiscountSwiftData" class="form-control" value="<?php echo htmlspecialchars($vendorDiscountSwiftData); ?>">
                                    </div>
                                </div>
                                <hr>
                                
                                <div class="d-grid gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary">Save Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingSpectranet">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpectranet" aria-expanded="false" aria-controls="collapseSpectranet">
                            <img src="../img/logo/spectranet_logo.jpg" alt="Spectranet" style="height: 20px; margin-right: 10px;"> Spectranet
                        </button>
                    </h2>
                    <div id="collapseSpectranet" class="accordion-collapse collapse" aria-labelledby="headingSpectranet" data-bs-parent="#settingsAccordion">
                        <div class="accordion-body">
                            <form method="POST"> 
                                <div class="row mt-3">
                                    <h5>Spectranet Bundle</h5>
                                    <div class="col-lg">
                                        <label for="buyingPriceSpectranetData">Buying Price &#8358;</label>
                                        <input type="text" id="buyingPriceSpectranetData" name="buyingPriceSpectranetData" class="form-control" value="<?php echo htmlspecialchars($buyingPriceSpectranetData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="sellingPriceSpectranetData">Selling Price &#8358;</label>
                                        <input type="text" id="sellingPriceSpectranetData" name="sellingPriceSpectranetData" class="form-control" value="<?php echo htmlspecialchars($sellingPriceSpectranetData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="profitSpectranetData">Profit &#8358;</label>
                                        <input type="text" id="profitSpectranetData" name="profitSpectranetData" class="form-control" value="<?php echo htmlspecialchars($profitSpectranetData); ?>">
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg">Discount (%)</div>
                                    <div class="col-lg">
                                        <label for="subscriberDiscountSpectranetData">Subscriber</label>
                                        <input type="text" id="subscriberDiscountSpectranetData" name="subscriberDiscountSpectranetData" class="form-control" value="<?php echo htmlspecialchars($subscriberDiscountSpectranetData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="agentDiscountSpectranetData">Agent</label>
                                        <input type="text" id="agentDiscountSpectranetData" name="agentDiscountSpectranetData" class="form-control" value="<?php echo htmlspecialchars($agentDiscountSpectranetData); ?>">
                                    </div>
                                    <div class="col-lg">
                                        <label for="vendorDiscountSpectranetData">Vendor</label>
                                        <input type="text" id="vendorDiscountSpectranetData" name="vendorDiscountSpectranetData" class="form-control" value="<?php echo htmlspecialchars($vendorDiscountSpectranetData); ?>">
                                    </div>
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary">Save Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>