<?php
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://dpeaceapp.com/api/airtime/?api_key=10b76799b73e9af37adbdcc560645b1c');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$server_response = curl_exec($ch);

curl_close($ch);

$server_response = json_decode($server_response);

echo "<pre>"; print_r($server_response); echo "</pre>";
?>