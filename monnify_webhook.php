<?php
include_once './_/conn.php';

// Monnify secret key (keep this secure)
$secretKey = 'VF62JU0QJJ37ECT4N1TJUEC4DPP09YJN';

// Log all incoming requests for debugging
$rawPayload = file_get_contents("php://input");
file_put_contents('webhook_debug_log.txt', "Incoming Request:\n" . $rawPayload . "\n", FILE_APPEND);

// Decode JSON (optional if needed later)
$data = json_decode($rawPayload, true);

// Get signature from header
$receivedSignature = $_SERVER['HTTP_MONNIFY_SIGNATURE'] ?? '';
$computedSignature = hash_hmac('sha512', $rawPayload, $secretKey);

// Verify signature
if (hash_equals($computedSignature, $receivedSignature)) {
    // Signature is valid, process the event
    $eventType = $data['eventType'] ?? '';
    $paymentRef = $data['eventData']['paymentReference'] ?? '';
    $amountPaid = $data['eventData']['amountPaid'] ?? 0;
    $email = $data['eventData']['customer']['email'] ?? '';

    // Log verified payload
    file_put_contents('webhook_debug_log.txt', "Verified Payload:\n" . print_r($data, true) . "\n", FILE_APPEND);

    // Retrieve user details from the database
    $user_sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($user_sql);
    if (!$stmt) {
        error_log("Error preparing statement: " . $conn->error);
        die("Database error. Please try again later.");
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        $userId = $user['id'];
        $fullname = ($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '');

        // Update user wallet with amount paid
        $update_sql = "UPDATE wallets SET balance = balance + ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_sql);
        if (!$stmt) {
            error_log("Error preparing statement: " . $conn->error);
            die("Database error. Please try again later.");
        }
        $stmt->bind_param("di", $amountPaid, $userId);
        if ($stmt->execute()) {
            echo "User balance updated successfully.";
        }

        // Log event to transactions table
        $user_id = $user['id'];
        $phone_number = $user['phone'];
        $product_description = "Wallet top-up with $amountPaid";
        $status = $data['eventData']['paymentStatus'] ?? 'Success';
        $amount = $amountPaid;
        $ref_id = $paymentRef;
        $type = 'Wallet Top-up';
        $insert_sql = "INSERT INTO transactions (user_id, fullname, phone_number, product_description, amount, status, transaction_ref, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        if (!$stmt) {
            error_log("Error preparing statement: " . $conn->error);
            die("Database error. Please try again later.");
        }
        $stmt->bind_param("isssdsss", $userId, $fullname, $phone_number, $product_description, $amountPaid, $status, $ref_id, $type);
        $stmt->execute();
    } else {
        echo "User not found.";
    }

    // Example action
    echo "✅ Verified webhook: $eventType | $email | ₦$amountPaid";

    // Log to file (optional)
    file_put_contents('webhook_log.txt', print_r([
        'status' => 'verified',
        'payload' => $data,
    ], true), FILE_APPEND);
} else {
    // Signature is invalid
    http_response_code(403); // Forbidden
    echo "❌ Invalid signature";

    // Log invalid signature
    file_put_contents('webhook_debug_log.txt', "Invalid Signature:\nExpected: $computedSignature\nReceived: $receivedSignature\nPayload: $rawPayload\n", FILE_APPEND);
}
?>

