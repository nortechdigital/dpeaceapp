<?php
function createVirtualAccount($user_id, $fullname, $email, $phone) {
    $banks = [
        'Monnify' => 'https://api.monnify.com/api/v1/bank-transfer/reserved-accounts',
        'Wema Bank' => 'https://api.wemabank.com/v1/virtual-accounts',
        'Sterling Bank' => 'https://api.sterling.ng/v1/virtual-accounts',
        'Access Bank' => 'https://api.accessbank.com/v1/virtual-accounts'
    ];

    $api_keys = [
        'Monnify' => 'YOUR_MONNIFY_API_KEY',
        'Wema Bank' => 'YOUR_WEMA_API_KEY',
        'Sterling Bank' => 'YOUR_STERLING_API_KEY',
        'Access Bank' => 'YOUR_ACCESS_API_KEY'
    ];

    $accounts = [];

    foreach ($banks as $bank_name => $api_url) {
        $data = [
            "accountReference" => uniqid("VA_"),
            "accountName" => $fullname,
            "customerEmail" => $email,
            "customerName" => $fullname,
            "customerPhoneNumber" => $phone,
            "preferredBanks" => [$bank_name],
        ];

        $headers = [
            "Authorization: Bearer " . $api_keys[$bank_name],
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);

        $response_data = json_decode($response, true);
        
        if ($response_data && isset($response_data['accountNumber'])) {
            $accounts[] = [
                'account_number' => $response_data['accountNumber'],
                'bank_name' => $bank_name
            ];
        }
    }

    return $accounts;
}
?>