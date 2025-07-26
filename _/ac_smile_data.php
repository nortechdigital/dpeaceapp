<?php
    session_start();
    include_once './_/conn.php';

    // Check if user is logged in, if not redirect to login page
    if (!isset($_SESSION['user_id'])) {
        header("Location: ./?page=login");
        exit;
    }

    $user_id = $_SESSION['user_id'];
    
    // Fetch wallet balance
    $query = "SELECT balance FROM wallets WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($balance);
    $stmt->fetch();
    $stmt->close();
 
    // Define the URL for the SOAP service
    $soap_url = "https://smile.com.ng/TPGW/ThirdPartyGateway";

    // Authentication Request XML
    $authentication_xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tpgw="http://xml.smilecoms.com/schema/TPGW">
        <soapenv:Header/>
        <soapenv:Body>
            <tpgw:Authenticate>
                <tpgw:Username>dadsmile5220</tpgw:Username>
                <tpgw:Password>Muslimat!4911</tpgw:Password>
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
    echo preg_match('/<SessionId>(.*?)<\/SessionId>/', $authentication_response, $matches);
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
    echo preg_match_all('/<Bundle>\s*<BundleTypeCode>(.*?)<\/BundleTypeCode>\s*<BundleDescription>(.*?)<\/BundleDescription>\s*<BundlePrice>(.*?)<\/BundlePrice>\s*<ValidityDays>(.*?)<\/ValidityDays>\s*<\/Bundle>/s', $bundle_catalogue_response, $bundle_matches, PREG_SET_ORDER);

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

        Confirm user account balance
        $account_balance = get_account_balance($session_id, $msisdn);
        
        if ($sWallet < $bundle_price && $ctn == 1) {
            echo "<script>alert('Error: Insufficient Account Balance.')</script>"; $ctn = 0;
            $transref = mt_rand(10000000000000, 99999999999999);
            $servicename = 'Smile Data';
            $servicedesc = "Purchase of SMILE $bundle_description, $bundle_price for Phone Number $msisdn";
            $profit = ($bundle_price * $smilediscount)/100;
            $sql = "INSERT INTO transactions (sId, transref, servicename, servicedesc, amount, status, oldbal, newbal, profit)
            VALUES ($sId, '$transref', '$servicename', '$servicedesc', '$bundle_price', 1, '$sWallet', '$new_balance', '0')";
            $conn->query($sql);
        }
        
    


?>

