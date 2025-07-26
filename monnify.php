<?php
$apiKey = "MK_TEST_NSAU2CN12T";
$clientSecret = "UGZ0RRHR69CWXE9D9KBUJWY8LSBNVRJ6";
$base_url = "https://sandbox.monnify.com"; // Replace with actual base URL

$authHeader = "Basic " . base64_encode("$apiKey:$clientSecret");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$base_url/api/v1/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: $authHeader",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$accessToken = $data['access_token'] ?? null; // Extract token

if ($accessToken) {
    echo "Access Token: " . $accessToken;
} else {
    echo "Failed to retrieve access token.";
}
?>

<?php
if (!$accessToken) {
    die("Access token not found. Please authenticate first.");
}

$bvnData = [
    "bvn" => "22222222226",
    "name" => "Benjamin Ranae RT",
    "dateOfBirth" => "03-Oct-1993",
    "mobileNo" => "08016857829"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$base_url/api/v1/vas/bvn-details-match");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bvnData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);

echo "BVN Verification Response: " . $response;
?>