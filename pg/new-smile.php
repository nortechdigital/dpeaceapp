<?php
// session_start();
include_once './_/conn.php';
include_once './_/ac_config.php';

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

$user_id = $_SESSION['user_id'];
        

// Define the URL for the SOAP service
$soap_url = "https://smile.com.ng/TPGW/ThirdPartyGateway";

// Authentication Request XML
$authentication_xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tpgw="http://xml.smilecoms.com/schema/TPGW">
    <soapenv:Header/>
    <soapenv:Body>
        <tpgw:Authenticate>
            <tpgw:Username>asarari</tpgw:Username>
            <tpgw:Password></tpgw:Password>
        </tpgw:Authenticate>
    </soapenv:Body>
</soapenv:Envelope>';

// Initialize cURL for Authentication Request
$ch = curl_init($soap_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $authentication_xml);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: text/xml;charset=UTF-8",
    "SOAPAction: \"http://xml.smilecoms.com/schema/TPGW/Authenticate\""
]);

// Execute the Authentication Request
$authentication_response = curl_exec($ch);
if (!$authentication_response) {
    die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
}

// Close cURL
curl_close($ch);

// Parse Session ID using Regex
preg_match('/<SessionId>(.*?)<\/SessionId>/', $authentication_response, $matches);
$session_id = $matches[1] ?? null;

if (!$session_id) {
    die("Error: No session ID found from authentication response.");
}

// Bundle Catalogue Query XML
$bundle_catalogue_xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tpgw="http://xml.smilecoms.com/schema/TPGW">
    <soapenv:Header/>
    <soapenv:Body>
        <tpgw:BundleCatalogueQuery>
            <tpgw:TPGWContext>
                <tpgw:SessionId>' . $session_id . '</tpgw:SessionId>
            </tpgw:TPGWContext>
        </tpgw:BundleCatalogueQuery>
    </soapenv:Body>
</soapenv:Envelope>';

// Initialize cURL for Bundle Catalogue Query
$ch = curl_init($soap_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $bundle_catalogue_xml);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: text/xml;charset=UTF-8",
    "SOAPAction: \"http://xml.smilecoms.com/schema/TPGW/BundleCatalogueQuery\""
]);

// Execute the Bundle Catalogue Query
$bundle_catalogue_response = curl_exec($ch);
if (!$bundle_catalogue_response) {
    die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
}

// Close cURL
curl_close($ch);

// Extract Bundle List using Regex
preg_match_all('/<Bundle>\s*<BundleTypeCode>(.*?)<\/BundleTypeCode>\s*<BundleDescription>(.*?)<\/BundleDescription>\s*<BundlePrice>(.*?)<\/BundlePrice>\s*<ValidityDays>(.*?)<\/ValidityDays>\s*<\/Bundle>/s', $bundle_catalogue_response, $bundle_matches, PREG_SET_ORDER);

// Display Extracted Bundle Data
if (empty($bundle_matches)) {
    die("Error: No bundle data found in the response.");
}

$slct = "<select id='smiledataplan' name='dataplan' required><option value=''>Select Smile Product</option>";
$btc = "<select id='bundle_type_code' name='bundle_type_code' required><option value=''>Select Smile Product</option>";

foreach ($bundle_matches as $bundle) {
    $prc = $bundle[3] / 100;
    $prc_txt = number_format($prc);
    $option = "<option value='" . htmlspecialchars($bundle[1]) . "' dataname='" . htmlspecialchars($bundle[2]) . "' networkname='SMILE' dataprice='" . htmlspecialchars($prc) . "'>" . htmlspecialchars($bundle[2]) . " for ₦" . htmlspecialchars($prc_txt) . " Validity " . htmlspecialchars($bundle[4]) . " days</option>";
    $slct .= $option;
    $btc .= $option;
}

$slct .= "</select>";
$btc .= "</select>";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $msisdn = $_POST['phone'] ?? null;
    $bundle_type_code = $_POST['bundle_type_code'] ?? null;
    $hash = $_POST['hash'] ?? '';
    $ctn = 1;
    $actype = $_POST['actype'];

    if (!$msisdn || !$bundle_type_code) {
        // die("Error: Please provide MSISDN and Bundle Type Code.");
        echo "<script>alert('Error: Please provide MSISDN and Bundle Type Code.')</script>"; $ctn = 0;
    }

    // Confirm if the mobile number is a Smile network number
    if ($ctn == 1 && $msisdn && !is_smile_number($msisdn) && $actype == 'PhoneNumber') {
        // die("Error: The provided mobile number is not a Smile network number.");
        echo "<script>alert('Error: The provided mobile number is not a Smile network number.')</script>"; $ctn = 0;
    }

    // Get the bundle price from the selected bundle
    foreach ($bundle_matches as $bundle) {
        if ($bundle[1] == $bundle_type_code) {
            $bundle_price = $bundle[3] / 100;
            $bundle_description = $bundle[2];
            break;
        }
    }

    if (!isset($bundle_price) && $ctn == 1) {
        echo "<script>alert('Error: Bundle price not found.')</script>"; $ctn = 0;
    }

    // Confirm user account balance
   // $account_balance = get_account_balance($session_id, $msisdn);
    
    // if ($sWallet < $bundle_price && $ctn == 1) {
    //     echo "<script>alert('Error: Insufficient Account Balance.')</script>"; $ctn = 0;
    //     $transref = mt_rand(10000000000000, 99999999999999);
    //     $servicename = 'Smile Data';
    //     $servicedesc = "Purchase of SMILE $bundle_description, $bundle_price for Phone Number $msisdn";
    //     $profit = ($bundle_price * $smilediscount)/100;
    //     $sql = "INSERT INTO transactions (sId, transref, servicename, servicedesc, amount, status, oldbal, newbal, profit)
    //     VALUES ($sId, '$transref', '$servicename', '$servicedesc', '$bundle_price', 1, '$sWallet', '$new_balance', '0')";
    //     $conn->query($sql);
    // }
    
  
if ($ctn === 1):
    // Deduct the bundle price from the user's account balance
    //$new_balance = $sWallet - $bundle_price;
    //update_account_balance($session_id, $msisdn, $new_balance);

    // Buy Bundle Request XML
    $buy_bundle_xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tpgw="http://xml.smilecoms.com/schema/TPGW">
        <soapenv:Header/>
        <soapenv:Body>
            <tpgw:BuyBundle>
                <tpgw:TPGWContext>
                    <tpgw:SessionId>' . $session_id . '</tpgw:SessionId>
                </tpgw:TPGWContext>
                ' . ($actype == 'PhoneNumber' ? '<tpgw:PhoneNumber>' . $msisdn . '</tpgw:PhoneNumber>' : '<tpgw:CustomerAccountId>' . $msisdn . '</tpgw:CustomerAccountId>') . '
                <tpgw:BundleTypeCode>' . $bundle_type_code . '</tpgw:BundleTypeCode>
                <tpgw:ChannelUsed>WEB</tpgw:ChannelUsed>
                <tpgw:UniqueTransactionId>' . uniqid() . '</tpgw:UniqueTransactionId>
                <tpgw:CustomerTenderedAmountInCents>' . ($bundle_price * 100) . '</tpgw:CustomerTenderedAmountInCents>
                <tpgw:Currency>NGN</tpgw:Currency>
                <tpgw:QuantityBought>1</tpgw:QuantityBought>
                <tpgw:Hash>' . $hash . '</tpgw:Hash>
            </tpgw:BuyBundle>
        </soapenv:Body>
    </soapenv:Envelope>';

    // Initialize cURL for Buy Bundle Request
    $ch = curl_init($soap_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $buy_bundle_xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: text/xml;charset=UTF-8",
        "SOAPAction: \"http://xml.smilecoms.com/schema/TPGW/BuyBundle\""
    ]);

    // Execute the Buy Bundle Request
    $buy_bundle_response = curl_exec($ch);
    if (!$buy_bundle_response) {
        echo 'Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch);
        exit();
    }

    // Close cURL
    curl_close($ch);

    // Extract Buy Bundle Response
    preg_match('/<Done>(.*?)<\/Done>/', $buy_bundle_response, $status_match);
    $transaction_status = $status_match[1] ?? "Unknown";

    // if ($ctn == 1 && $transaction_status == "true") {
        // UPDATE BALANCE - START
        // $sql = "UPDATE subscribers SET sWallet=$new_balance WHERE sId=$sId";
        // $conn->query($sql);
        
        // $transref = mt_rand(10000000000000, 99999999999999);
        // $servicename = 'Smile Data';
        // $servicedesc = "Purchase of SMILE $bundle_description, $bundle_price for $actype: $msisdn";
        // $profit = ($bundle_price * $smilediscount)/100;
        
        // Fetch current sRefWallet value
        // $sql = "SELECT sRefWallet FROM subscribers WHERE sId=$sId";
        // $result = $conn->query($sql);
        // if ($result->num_rows > 0) {
        //     $row = $result->fetch_assoc();
        //     $current_sRefWallet = $row['sRefWallet'];
        // } else {
        //     $current_sRefWallet = 0;
        // }

        // Add profit to sRefWallet
        // $new_sRefWallet = $current_sRefWallet + $profit;
        // $sql = "UPDATE subscribers SET sRefWallet=$new_sRefWallet WHERE sId=$sId";
        // $conn->query($sql);

        // Insert transaction into the database
        // $sql = "INSERT INTO transactions (sId, transref, servicename, servicedesc, amount, status, oldbal, newbal, profit)
        // VALUES ($sId, '$transref', '$servicename', '$servicedesc', '$bundle_price', 0, '$sWallet', '$new_balance', '$profit')";
        // $conn->query($sql);
        
        echo "<script>alert('Purchase of SMILE $bundle_description, N$bundle_price for $actype: $msisdn successful! Bonus: N$profit'); window.location.href = window.location.href;</script>";
        exit();
    } else {
        echo "<script>alert('Purchase failed. Please try again.'); window.location.href = window.location.href;</script>";
        exit();
    }
endif;

    
}

function is_smile_number($msisdn) {
    // Normalize the number by removing spaces and ensuring a consistent format
    $msisdn = preg_replace('/\s+/', '', $msisdn);
    // Check if the number starts with '2347020' or '07020'
    return preg_match('/^(2347020|07020)/', $msisdn) === 1;
    return true;
}

function get_account_balance($session_id, $msisdn) {
    // Implement logic to get the user's account balance
    return 1000;
}

function update_account_balance($session_id, $msisdn, $new_balance) {
    // Implement logic to update the user's account balance
    // Placeholder:
}

?>

<div class="page-content header-clear-medium">
        
        <div class="card card-style">
            
            <div class="content">
                <div class="text-center"><img src="../../assets/images/icons/smile.png" width="60" height="60"></div> 
                 <!-- <h1 class="text-center mt-3">Buy Smile | &#8358;<?= number_format($sWallet) ?></h1>  -->
                <h1 class="text-center mt-3">Buy Smile</h1>
                <hr/>
                <form method="post">
                    <fieldset>

                        <div class="input-style input-style-always-active has-borders mb-4">
                            <label for="actype" class="color-theme opacity-80 font-700 font-12">Account Type</label>  
                            <select id="actype" name="actype">
                                <option value="" disabled="" selected="">Select Account Type</option> 
                                <option value='PhoneNumber'>Phone Number</option>
                                <option value='AccountNumber'>Account Number</option>
                            </select>
                            <span><i class="fa fa-chevron-down"></i></span>
                            <i class="fa fa-check disabled valid color-green-dark"></i>
                            <i class="fa fa-check disabled invalid color-red-dark"></i>
                            <em></em>
                        </div>
                        
                        <div class="input-style has-borders validate-field mb-4 input-box">
                            <label for="phone" class="color-theme opacity-80 font-700 font-12 smile-phonet">Phone Number</label>
                            <label for="phone" class="color-theme opacity-80 font-700 font-12 smile-act">Account Number(10 digit)</label>
                            <input type="text" onkeyup="verifyNetwork()" name="phone" placeholder="" value="" class="round-small smile-phone" id="phone" required />
                            <!-- <input type="hidden" value="<?php echo $data2; ?>" id="smilediscount" /> -->
                        </div>

                        <div class="input-style input-style-always-active has-borders mb-4">
                            <label for="smiledataplan" class="color-theme opacity-80 font-700 font-12">Product</label>
                            <?= $btc ?>
                            <span><i class="fa fa-chevron-down"></i></span>
                            <i class="fa fa-check disabled valid color-green-dark"></i>
                            <i class="fa fa-check disabled invalid color-red-dark"></i> 
                            <em></em>
                        </div>

                        <!-- <div class="input-style input-style-always-active has-borders validate-field mb-4"> -->
                            <!-- <label for="smileamounttopay" class="color-theme opacity-80 font-700 font-12">Bonus You Will Get (<?php echo $smilediscount; ?>%)</label> -->
                            <!--<input type="text" name="smileamounttopay" placeholder="Bonus" value="" class="round-small" id="smileamounttopay" required readonly />-->
                        <!-- </div> -->

                        
                        <input name="transkey" id="transkey" type="hidden" />
                        <input type="hidden" id="hash" name="hash" value="">

                        <div class="form-button py-1">
                            <button type="submit" id="data-btn" name="purchase-smile-data" style="width: 100%;" class="btn btn-full btn-l font-600 font-15 gradient-highlight mt-4 rounded-s">
                                Buy Smile
                            </button>
                        </div>
                    </fieldset>
                </form>        
            </div>

        </div>

</div>