<?php
// Define the URL with the parameters
$url = "http://swiftng.com:3000/ISWPayment.ashx?Username=PeaceAppTest&Password=peaceapptest&Partner=PeaceApp";

// Make the GET request
$response = file_get_contents($url);

// Check if the request was successful
if ($response !== false) {
    echo "Response: " . $response;
} else {
    echo "There was an error in the request.";
}
?>
