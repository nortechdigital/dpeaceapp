<?php
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://airtimenigeria.com/api/v1/airtime',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array('network_operator' => 'mtn','phone' =>
  '08076007676','amount' => '10000','max_amount' => '9700'),
  CURLOPT_HTTPHEADER => array(
  'Accept: application/json',
  'Content-Type: application/json',
  'Authorization: Bearer 1|yjm8D7QYXuetbdsVYCnYodclM1K47ilufL5OrH6N'
  ),
));
