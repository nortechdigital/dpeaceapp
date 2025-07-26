<?php
class GloNigeriaClient {
    private $wsdl = 'http://41.203.65.10:8913/topupservice/service?wsdl';
    private $client;
    private $context = [
        'channel' => 'WSClient',
        'clientId' => 'ERS',
        'clientRequestTimeout' => 500,
        'password' => 'M@deformor3'
    ];
    private $resellerId = 'WEB8156881490';
    private $userId = '9900';

    public function __construct() {
        $this->client = new SoapClient($this->wsdl, [
            'trace' => 1,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE
        ]);
    }

    private function buildContext($additional = []) {
        return array_merge($this->context, [
            'initiatorPrincipalId' => [
                'id' => $this->resellerId,
                'type' => 'RESELLERUSER',
                'userId' => $this->userId
            ],
            'clientReference' => uniqid()
        ], $additional);
    }

    public function requestTopup($recipientId, $productId, $amount, $currency = 'NGN', $accountType = 'DATA_BUNDLE') {
        $params = [
            'context' => $this->buildContext(),
            'senderPrincipalId' => [
                'id' => $this->resellerId,
                'type' => 'RESELLERUSER',
                'userId' => $this->userId
            ],
            'topupPrincipalId' => [
                'id' => $recipientId,
                'type' => 'SUBSCRIBERMSISDN'
            ],
            'senderAccountSpecifier' => [
                'accountId' => $this->resellerId,
                'accountTypeId' => 'RESELLER'
            ],
            'topupAccountSpecifier' => [
                'accountId' => $recipientId,
                'accountTypeId' => $accountType
            ],
            'productId' => $productId,
            'amount' => [
                'currency' => $currency,
                'value' => $amount
            ]
        ];

        return $this->client->requestTopup($params);
    }

    public function requestVoucher($recipientId, $voucherType, $amount, $currency = 'NGN') {
        $params = [
            'context' => $this->buildContext([
                'transactionProperties' => [
                    'entry' => [
                        ['key' => 'productSKU', 'value' => $voucherType],
                        ['key' => 'currency', 'value' => $currency],
                        ['key' => 'purchaseAmount', 'value' => $amount]
                    ]
                ]
            ]),
            'senderPrincipalId' => [
                'id' => $this->resellerId,
                'type' => 'RESELLERUSER',
                'userId' => $this->userId
            ],
            'receiverPrincipalId' => [
                'id' => $recipientId,
                'type' => 'SUBSCRIBERMSISDN'
            ],
            'senderAccountSpecifier' => [
                'accountTypeId' => 'RESELLER'
            ],
            'purchaseOrder' => [
                'productSpecifier' => [
                    'productId' => $voucherType,
                    'productIdType' => 'VOD'
                ],
                'purchaseCount' => 1
            ]
        ];

        return $this->client->requestPurchase($params);
    }

public function getResultDescription($code) {
    $resultCodes = [
        0 => 'SUCCESS',
        1 => 'PENDING_APPROVAL',
        20 => 'AUTHENTICATION_FAILED',
        37 => 'INITIATOR_PRINCIPAL_NOT_FOUND',
        104 => 'INSUFFICIENT_SENDER_CREDIT',
        2016 => 'TRANSACTION_ALREADY_COMPLETED',
        // Add more codes as needed
    ];
    return $resultCodes[$code] ?? 'UNKNOWN_ERROR';
}

public function getLastRequest() {
    return $this->client->__getLastRequest();
}

public function getLastResponse() {
    return $this->client->__getLastResponse();
}

}