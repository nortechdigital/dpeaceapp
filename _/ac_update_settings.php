<?php
session_start();
include "../conn.php";

$settings = [
    'buyingPriceData' => isset($_POST['buyingPriceData']) ? $_POST['buyingPriceData'] : '',
    'sellingPriceData' => isset($_POST['sellingPriceData']) ? $_POST['sellingPriceData'] : '',
    'profitData' => isset($_POST['profitData']) ? $_POST['profitData'] : '',
    'subscriberDiscountData' => isset($_POST['subscriberDiscountData']) ? $_POST['subscriberDiscountData'] : '',
    'agentDiscountData' => isset($_POST['agentDiscountData']) ? $_POST['agentDiscountData'] : '',
    'vendorDiscountData' => isset($_POST['vendorDiscountData']) ? $_POST['vendorDiscountData'] : '',
    'buyingPriceAirtime' => isset($_POST['buyingPriceAirtime']) ? $_POST['buyingPriceAirtime'] : '',
    'sellingPriceAirtime' => isset($_POST['sellingPriceAirtime']) ? $_POST['sellingPriceAirtime'] : '',
    'profitAirtime' => isset($_POST['profitAirtime']) ? $_POST['profitAirtime'] : '',
    'subscriberDiscountAirtime' => isset($_POST['subscriberDiscountAirtime']) ? $_POST['subscriberDiscountAirtime'] : '',
    'agentDiscountAirtime' => isset($_POST['agentDiscountAirtime']) ? $_POST['agentDiscountAirtime'] : '',
    'vendorDiscountAirtime' => isset($_POST['vendorDiscountAirtime']) ? $_POST['vendorDiscountAirtime'] : '',
    'buyingPriceDataAirtel' => isset($_POST['buyingPriceDataAirtel']) ? $_POST['buyingPriceDataAirtel'] : '',
    'sellingPriceDataAirtel' => isset($_POST['sellingPriceDataAirtel']) ? $_POST['sellingPriceDataAirtel'] : '',
    'profitDataAirtel' => isset($_POST['profitDataAirtel']) ? $_POST['profitDataAirtel'] : '',
    'subscriberDiscountDataAirtel' => isset($_POST['subscriberDiscountDataAirtel']) ? $_POST['subscriberDiscountDataAirtel'] : '',
    'agentDiscountDataAirtel' => isset($_POST['agentDiscountDataAirtel']) ? $_POST['agentDiscountDataAirtel'] : '',
    'vendorDiscountDataAirtel' => isset($_POST['vendorDiscountDataAirtel']) ? $_POST['vendorDiscountDataAirtel'] : '',
    'buyingPriceAirtimeAirtel' => isset($_POST['buyingPriceAirtimeAirtel']) ? $_POST['buyingPriceAirtimeAirtel'] : '',
    'sellingPriceAirtimeAirtel' => isset($_POST['sellingPriceAirtimeAirtel']) ? $_POST['sellingPriceAirtimeAirtel'] : '',
    'profitAirtimeAirtel' => isset($_POST['profitAirtimeAirtel']) ? $_POST['profitAirtimeAirtel'] : '',
    'subscriberDiscountAirtimeAirtel' => isset($_POST['subscriberDiscountAirtimeAirtel']) ? $_POST['subscriberDiscountAirtimeAirtel'] : '',
    'agentDiscountAirtimeAirtel' => isset($_POST['agentDiscountAirtimeAirtel']) ? $_POST['agentDiscountAirtimeAirtel'] : '',
    'vendorDiscountAirtimeAirtel' => isset($_POST['vendorDiscountAirtimeAirtel']) ? $_POST['vendorDiscountAirtimeAirtel'] : '',
    'buyingPriceDataGlo' => isset($_POST['buyingPriceDataGlo']) ? $_POST['buyingPriceDataGlo'] : '',
    'sellingPriceDataGlo' => isset($_POST['sellingPriceDataGlo']) ? $_POST['sellingPriceDataGlo'] : '',
    'profitDataGlo' => isset($_POST['profitDataGlo']) ? $_POST['profitDataGlo'] : '',
    'subscriberDiscountDataGlo' => isset($_POST['subscriberDiscountDataGlo']) ? $_POST['subscriberDiscountDataGlo'] : '',
    'agentDiscountDataGlo' => isset($_POST['agentDiscountDataGlo']) ? $_POST['agentDiscountDataGlo'] : '',
    'vendorDiscountDataGlo' => isset($_POST['vendorDiscountDataGlo']) ? $_POST['vendorDiscountDataGlo'] : '',
    'buyingPriceAirtimeGlo' => isset($_POST['buyingPriceAirtimeGlo']) ? $_POST['buyingPriceAirtimeGlo'] : '',
    'sellingPriceAirtimeGlo' => isset($_POST['sellingPriceAirtimeGlo']) ? $_POST['sellingPriceAirtimeGlo'] : '',
    'profitAirtimeGlo' => isset($_POST['profitAirtimeGlo']) ? $_POST['profitAirtimeGlo'] : '',
    'subscriberDiscountAirtimeGlo' => isset($_POST['subscriberDiscountAirtimeGlo']) ? $_POST['subscriberDiscountAirtimeGlo'] : '',
    'agentDiscountAirtimeGlo' => isset($_POST['agentDiscountAirtimeGlo']) ? $_POST['agentDiscountAirtimeGlo'] : '',
    'vendorDiscountAirtimeGlo' => isset($_POST['vendorDiscountAirtimeGlo']) ? $_POST['vendorDiscountAirtimeGlo'] : '',
    'buyingPriceData9mobile' => isset($_POST['buyingPriceData9mobile']) ? $_POST['buyingPriceData9mobile'] : '',
    'sellingPriceData9mobile' => isset($_POST['sellingPriceData9mobile']) ? $_POST['sellingPriceData9mobile'] : '',
    'profitData9mobile' => isset($_POST['profitData9mobile']) ? $_POST['profitData9mobile'] : '',
    'subscriberDiscountData9mobile' => isset($_POST['subscriberDiscountData9mobile']) ? $_POST['subscriberDiscountData9mobile'] : '',
    'agentDiscountData9mobile' => isset($_POST['agentDiscountData9mobile']) ? $_POST['agentDiscountData9mobile'] : '',
    'vendorDiscountData9mobile' => isset($_POST['vendorDiscountData9mobile']) ? $_POST['vendorDiscountData9mobile'] : '',
    'buyingPriceAirtime9mobile' => isset($_POST['buyingPriceAirtime9mobile']) ? $_POST['buyingPriceAirtime9mobile'] : '',
    'sellingPriceAirtime9mobile' => isset($_POST['sellingPriceAirtime9mobile']) ? $_POST['sellingPriceAirtime9mobile'] : '',
    'profitAirtime9mobile' => isset($_POST['profitAirtime9mobile']) ? $_POST['profitAirtime9mobile'] : '',
    'subscriberDiscountAirtime9mobile' => isset($_POST['subscriberDiscountAirtime9mobile']) ? $_POST['subscriberDiscountAirtime9mobile'] : '',
    'agentDiscountAirtime9mobile' => isset($_POST['agentDiscountAirtime9mobile']) ? $_POST['agentDiscountAirtime9mobile'] : '',
    'vendorDiscountAirtime9mobile' => isset($_POST['vendorDiscountAirtime9mobile']) ? $_POST['vendorDiscountAirtime9mobile'] : '',
    'buyingPriceSmile' => isset($_POST['buyingPriceSmile']) ? $_POST['buyingPriceSmile'] : '',
    'sellingPriceSmile' => isset($_POST['sellingPriceSmile']) ? $_POST['sellingPriceSmile'] : '',
    'profitSmile' => isset($_POST['profitSmile']) ? $_POST['profitSmile'] : '',
    'subscriberDiscountSmile' => isset($_POST['subscriberDiscountSmile']) ? $_POST['subscriberDiscountSmile'] : '',
    'agentDiscountSmile' => isset($_POST['agentDiscountSmile']) ? $_POST['agentDiscountSmile'] : '',
    'vendorDiscountSmile' => isset($_POST['vendorDiscountSmile']) ? $_POST['vendorDiscountSmile'] : '',
    'buyingPriceSwift' => isset($_POST['buyingPriceSwift']) ? $_POST['buyingPriceSwift'] : '',
    'sellingPriceSwift' => isset($_POST['sellingPriceSwift']) ? $_POST['sellingPriceSwift'] : '',
    'profitSwift' => isset($_POST['profitSwift']) ? $_POST['profitSwift'] : '',
    'subscriberDiscountSwift' => isset($_POST['subscriberDiscountSwift']) ? $_POST['subscriberDiscountSwift'] : '',
    'agentDiscountSwift' => isset($_POST['agentDiscountSwift']) ? $_POST['agentDiscountSwift'] : '',
    'vendorDiscountSwift' => isset($_POST['vendorDiscountSwift']) ? $_POST['vendorDiscountSwift'] : '',
    'buyingPriceSpectranet' => isset($_POST['buyingPriceSpectranet']) ? $_POST['buyingPriceSpectranet'] : '',
    'sellingPriceSpectranet' => isset($_POST['sellingPriceSpectranet']) ? $_POST['sellingPriceSpectranet'] : '',
    'profitSpectranet' => isset($_POST['profitSpectranet']) ? $_POST['profitSpectranet'] : '',
    'subscriberDiscountSpectranet' => isset($_POST['subscriberDiscountSpectranet']) ? $_POST['subscriberDiscountSpectranet'] : '',
    'agentDiscountSpectranet' => isset($_POST['agentDiscountSpectranet']) ? $_POST['agentDiscountSpectranet'] : '',
    'vendorDiscountSpectranet' => isset($_POST['vendorDiscountSpectranet']) ? $_POST['vendorDiscountSpectranet'] : ''
];

foreach ($settings as $key => $value) {
  echo  $sql = "UPDATE settings SET value = '$value' WHERE name = '$key'"; die;
    if (mysqli_affected_rows($conn) == 0) {
        $sql = "INSERT INTO settings (name, value) VALUES ('$key', '$value')";
    }
    mysqli_query($conn, $sql);
}

header("Location: ./?page=settings");
exit;
?>
