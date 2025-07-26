<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include_once './_/conn.php';
include_once './_/ac_config.php';

function requery($requestId, $userId) {
    $conn = $GLOBALS['conn'];
    // $requestId = "20250610145857"; // Original requestId from payment/validation

    // Endpoint should point to `requery` (GET method)
    $endpoint = VAS2NETS_BASE_URL . VAS2NETS_ENV_URI . "requery?" . http_build_query(['requestId' => $requestId]);

    // Initialize cURL (GET request)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_USERPWD, VAS2NETS_USERNAME . ":" . VAS2NETS_PASSWORD);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    // Execute
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        die("cURL Error: " . curl_error($ch));
    }
    curl_close($ch);

    // Process response
    if ($httpCode == 200) {
        $responseData = json_decode($response, true);
        // echo "Status: " . $responseData['status'] . "\n";
        // echo "Description: " . $responseData['description'] . "\n";
        if (isset($responseData['data'])) {
            // echo "Transaction Status: " . $responseData['data']['status'] . "\n";
            $status = $responseData['data']['status'];
            if ($status === 'Pending') {
                echo "Transaction is still $status";
            } else {
                $balance = 0;
                $sql = "UPDATE transactions SET status = '" . strtolower($status) . "' WHERE transaction_ref = '$requestId'";
                if ($conn->query($sql) === true) {
                    if ($status === 'Failed') {
                        $sql = "SELECT * FROM wallets WHERE user_id=$userId";
                        $rs = $conn->query($sql);
                        if ($rs && $rs->num_rows > 0) {
                            while ($row = $rs->fetch_assoc()) {
                                $balance += $row['balance'];
                            }
                            $sql = "UPDATE wallets SET balance = $balance WHERE user_id=$userId";
                            $conn->query($sql);
                        }
                    }
                }
            }
        }
    } else {
        echo "HTTP Error: " . $httpCode . "\n";
        echo "Response: " . $response . "\n";
    }
}

$sql = "SELECT id, user_id, transaction_ref, amount FROM transactions
WHERE type IN ('Electricity Subscription', 'TV Subscription', 'Airtime Purchase', 'Data Purchase')
  AND status = 'pending' ORDER BY created_at DESC LIMIT 10";

$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Process each $row as needed
        // print_r($row);
        requery($row['transaction_ref'], $row['user_id']);
    }
} else {
    echo "Query Error: " . mysqli_error($conn);
}
?>