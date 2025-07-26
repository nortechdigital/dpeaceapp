<?php
// Define the required variables
$baseUrl = "https://b2bapi.v2napi.com/"; // Replace with the actual base URL
$envUri = "dev/";   // Replace with the environment URI
$metaSubUri = "meta/"; // Replace with the meta sub URI

$url = "{$baseUrl}{$envUri}{$metaSubUri}getBillerCategories";

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
        // echo "Profile Details:\n";
        // print_r($data);

        // Decode JSON into a PHP associative array

        $categories = $data['data']['categories'];
        
    } else {
        // Handle error response (e.g., invalid credentials)
        echo "Error: " . $response_code . "\n";
        echo "Message: " . $response . "\n";
    }
}

// Close the cURL session
curl_close($ch);
?>

<div class="container">
    <h1>Biller Categories</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Category ID</th>
                <th>Category Name</th>
                <th>Image</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category) : ?>
                <tr>
                    <td><?php echo $category['id']; ?></td>
                    <td><?php echo $category['name']; ?></td>
                    <td><img src="<?= $category['imagePath'] ?>" alt="<?= $category['name']; ?>" width="150"></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>