<?php
require __DIR__ . '/vendor/autoload.php';
use phpseclib3\Crypt\RSA;

// Generate new RSA key pair
$privateKey = RSA::createKey(2048);
$publicKey = $privateKey->getPublicKey();

// Save keys to files
file_put_contents('private.pem', $privateKey->toString('PKCS8'));
file_put_contents('public.pem', $publicKey->toString('PKCS8'));

echo "Successfully generated new key pairs:\n";
echo "- private.pem\n- public.pem\n";
echo "Store these securely and never share your private key!\n";