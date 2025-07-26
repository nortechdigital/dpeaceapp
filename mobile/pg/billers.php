<?php
// Define the required variables
$baseUrl = "https://b2bapi.v2napi.com/"; // Replace with the actual base URL
$envUri = "dev/";   // Replace with the environment URI
$metaSubUri = "meta/"; // Replace with the meta sub URI

$url = "{$baseUrl}{$envUri}{$metaSubUri}getAllBillers";

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

        // Decode JSON into a PHP associative array
        $dt = $data['data'];

        // print_r($dt);
        
    } else {
        // Handle error response (e.g., invalid credentials)
        echo "Error: " . $response_code . "\n";
        echo "Message: " . $response . "\n";
    }
}

// Close the cURL session
curl_close($ch);


// output of print_r($dt);
?>

<style>
    .category {
        margin-bottom: 20px;
    }
    .biller {
        border: 1px solid #ddd;
        padding: 10px;
        margin-bottom: 10px;
    }
    .biller img {
        display: block;
        margin-top: 10px;
    }
</style>

<div class="container">
    <?php if (!empty($dt)) : ?>
        <?php foreach ($dt as $category) : ?>
            <div class="category">
                <h3><?php echo htmlspecialchars($category['category']); ?></h3>
                <p><?php echo htmlspecialchars($category['description']); ?></p>
                <div class="billers">
                    <?php if (!empty($category['billers'])) : ?>
                        <?php foreach ($category['billers'] as $biller) : ?>
                            <div class="biller">
                                <h4><?php echo htmlspecialchars($biller['name']); ?></h4>
                                <p>Status: <?php echo htmlspecialchars($biller['status']); ?></p>
                                <p>Info: <?php echo htmlspecialchars($biller['info']); ?></p>
                                <img src="<?php echo htmlspecialchars($biller['imagePath']); ?>" alt="<?php echo htmlspecialchars($biller['name']); ?>" style="max-width: 100px;">
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p>No billers available for this category.</p>
                    <?php endif; ?>
                </div>
                <hr>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>No data available.</p>
    <?php endif; ?>
</div>