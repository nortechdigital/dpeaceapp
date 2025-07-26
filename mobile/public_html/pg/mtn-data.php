<?php
// Define the required variables
$baseUrl = "https://b2bapi.v2napi.com/"; // Replace with the actual base URL
$envUri = "dev/";   // Replace with the environment URI
// $metaSubUri = "meta/"; // Replace with the meta sub URI

$url = "{$baseUrl}{$envUri}bouquet/vtu/MTN-DATA"; // Final URL to be called

// Define username and password
$username = "dPeaceApp"; // Replace with actual username
$password = "D$34eace_App$#"; // Replace with actual password

// Set up the headers (No need for Authorization header when using Basic Auth)
$headers = [
    "Content-Type: application/json",
];

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);  // Basic Authentication
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Optional: Verify SSL certificate


// Execute the request and get the response
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
} else {
    // Handle the response (successful or error)
    $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($response_code == 200) {
        // Successfully received data, parse the response
        $data = json_decode($response, true);
        $data = $data['data'];
        // print_r($data);
    } else {
        // Handle error response (e.g., invalid credentials)
        echo "Error: " . $response_code . "\n";
        echo "Message: " . $response . "\n";
    }
}

// Close the cURL session
curl_close($ch);


// output of print_r($data);
?>

<div class="container">
    <h1>MTN Data Bouquets</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['bouquets'] as $bouquet) : ?>
                <tr>
                    <td><?= $bouquet['code'] ?></td>
                    <td><?= $bouquet['name'] ?></td>
                    <td><?= $bouquet['description'] ?></td>
                    <td><?= $bouquet['price'] ?></td>
                    <td><?= $bouquet['type'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>