<?php

function generateRSAKeyPair() {
    $config = [
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];

    // Generate the private and public key
    $res = openssl_pkey_new($config);

    // Extract private key
    openssl_pkey_export($res, $privateKey);

    // Extract public key
    $publicKeyDetails = openssl_pkey_get_details($res);
    $publicKey = $publicKeyDetails["key"];

    return [
        "privateKey" => $privateKey,
        "publicKey" => $publicKey,
    ];
}

// Example usage
$keyPair = generateRSAKeyPair();

echo "Private Key:\n";
echo $keyPair['privateKey'];
echo "\n\nPublic Key:\n";
echo $keyPair['publicKey'];
?>
