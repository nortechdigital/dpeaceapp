<?php
function is_smile_number($phone_number) {
    // Define Smile network prefixes
    $smile_prefixes = ['+2347020', '07020']; // Add actual Smile prefixes here

    // Normalize the phone number (remove spaces, dashes, etc.)
    $normalized_number = preg_replace('/\D/', '', $phone_number);

    // Check if the phone number starts with any Smile prefix
    foreach ($smile_prefixes as $prefix) {
        if (strpos($normalized_number, $prefix) === 0) {
            return true;
        }
    }

    return false;
}
