<?php
$postData = json_encode([
 "phone" => "09061680055",
 "package_code" => "mtn_sme_10gb",
 "max_amount" => "2200",
 "process_type" => "instant",
 "customer_reference" => "HSYTI78HS0",
 "callback_url" => '',
], JSON_THROW_ON_ERROR);
$curl = curl_init();
curl_setopt_array($curl, array(
 CURLOPT_URL => 'https://www.airtimenigeria.com/api/v1/data',
 CURLOPT_RETURNTRANSFER => true,
 CURLOPT_ENCODING => '',
 CURLOPT_MAXREDIRS => 10,
 CURLOPT_TIMEOUT => 0,
 CURLOPT_FOLLOWLOCATION => true,
 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
 CURLOPT_CUSTOMREQUEST => 'POST',
 CURLOPT_POSTFIELDS => $postData,
 CURLOPT_HTTPHEADER => array(
 'Authorization: Bearer 869|USP6l30uOmAuhwuzF35viyAjlxeSJ9qSVpNej7dQ',
 'Content-Type: application/json',
 'Accept: application/json'
 ),
));
$response = curl_exec($curl);
curl_close($curl);
echo $response;