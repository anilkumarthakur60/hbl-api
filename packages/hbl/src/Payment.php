<?php

namespace Anil\Hbl;

use Carbon\Carbon;

class Payment extends ActionRequest
{
    public function ExecuteFormJoses($amt, $orderNo, $orderDescription, $additional_data = [], $purchaseItems = []): string
    {
        $custom_fields = [];
        if (! empty($additional_data)) {
            foreach ($additional_data as $key => $value) {
                $custom_fields[] = [
                    'fieldName' => $key,
                    'fieldValue' => $value,
                ];
            }
        }
        $now = Carbon::now();
        $amt = round($amt, config('hbl.decimal_places'));
        $textAmount = str_pad(($amt == null ? 0 : $amt) * 100, 12, '0', STR_PAD_LEFT);

        $request = [
            'apiRequest' => [
                'requestMessageID' => $this->Guid(),
                'requestDateTime' => $now->utc()->toIso8601String(),
                'language' => config('hbl.language'),
            ],
            'officeId' => config('hbl.OfficeId'),
            'orderNo' => $orderNo,
            'productDescription' => $orderDescription,
            'paymentType' => config('hbl.payment_type'),
            'paymentCategory' => config('hbl.payment_category'),
            'storeCardDetails' => [
                'storeCardFlag' => config('hbl.store_card_flag'),
                'storedCardUniqueID' => $this->Guid(),
            ],
            'installmentPaymentDetails' => [
                'ippFlag' => 'N',
                'installmentPeriod' => 0,
                'interestType' => null,
            ],
            'mcpFlag' => config('hbl.mcp_flag'),
            'request3dsFlag' => config('hbl.Input3DS'),
            'transactionAmount' => [
                'amountText' => $textAmount,
                'currencyCode' => config('hbl.InputCurrency'),
                'decimalPlaces' => 2,
                'amount' => $amt,
            ],
            'notificationURLs' => [
                'confirmationURL' => config('hbl.redirect_url.success'),
                'failedURL' => config('hbl.redirect_url.failed'),
                'cancellationURL' => config('hbl.redirect_url.cancel'),
                'backendURL' => config('hbl.redirect_url.backend'),
            ],
            'deviceDetails' => config('hbl.device_details'),
            'purchaseItems' => $purchaseItems,
            'customFieldList' => $custom_fields,
        ];

        $payload = [
            'request' => $request,
            'iss' => config('hbl.AccessToken'),
            'aud' => 'PacoAudience',
            'CompanyApiKey' => config('hbl.AccessToken'),
            'iat' => $now->unix(),
            'nbf' => $now->unix(),
            'exp' => $now->addHour()->unix(),
        ];

        $stringPayload = json_encode($payload);
        $signingKey = $this->GetPrivateKey(SecurityData::$MerchantSigningPrivateKey);
        $encryptingKey = $this->GetPublicKey(SecurityData::$PacoEncryptionPublicKey);

        $body = $this->EncryptPayload($stringPayload, $signingKey, $encryptingKey);

        // third-party http client https://github.com/guzzle/guzzle
        $response = $this->client->post('api/2.0/Payment/prePaymentUi', [
            'headers' => [
                'Accept' => 'application/jose',
                'CompanyApiKey' => config('hbl.AccessToken'),
                'Content-Type' => 'application/jose; charset=utf-8',
            ],
            'body' => $body,
        ]);

        $token = $response->getBody()->getContents();
        $decryptingKey = $this->GetPrivateKey(SecurityData::$MerchantDecryptionPrivateKey);
        $signatureVerificationKey = $this->GetPublicKey(SecurityData::$PacoSigningPublicKey);

        return $this->DecryptToken($token, $decryptingKey, $signatureVerificationKey);
    }

    public function executeFormJose(array $paymentObj = []): string
    {
        try {
            $now = Carbon::now();

            $custom_fields = [];
            if (isset($paymentObj['custom_fields']) && !empty($paymentObj['custom_fields'])) {
                foreach ($paymentObj['custom_fields'] as $key => $value) {
                    $custom_fields[] = [
                        "fieldName" => $key,
                        "fieldValue" => $value
                    ];
                }
            }

            $request = [
                "apiRequest" => [
                    "requestMessageID" => $this->Guid(),
                    "requestDateTime" => $now->utc()->format('Y-m-d\TH:i:s.v\Z'),
                    "language" => "en-US",
                ],
                "officeId" => config('hbl.OfficeId'),
                "orderNo" => $paymentObj['order_no'],
                "productDescription" => "Booking Payment",
                "paymentType" => "CC",
                "paymentCategory" => "ECOM",
                "storeCardDetails" => [
                    "storeCardFlag" => "N",
                    "storedCardUniqueID" => $this->Guid()
                ],
                "installmentPaymentDetails" => [
                    "ippFlag" => "N",
                    "installmentPeriod" => 0,
                    "interestType" => null
                ],
                "mcpFlag" => "N",
                "request3dsFlag" => config('hbl.Input3DS'),
                "transactionAmount" => [
                    "amountText" => str_pad(($paymentObj['amount'] == null ? 0 : $paymentObj['amount']) * 100, 12, "0", STR_PAD_LEFT),
                    "currencyCode" => config('hbl.InputCurrency'),
                    "decimalPlaces" => 2,
                    "amount" => $paymentObj['amount']
                ],
                "notificationURLs" => [
                    "confirmationURL" => $paymentObj['success_url'],
                    "failedURL" => $paymentObj['failed_url'],
                    "cancellationURL" => $paymentObj['cancel_url'],
                    "backendURL" => $paymentObj['backend_url']
                ],
                "deviceDetails" => [
                    "browserIp" => "1.0.0.1",
                    "browser" => "Postman Browser",
                    "browserUserAgent" => "PostmanRuntime/7.26.8 - not from header",
                    "mobileDeviceFlag" => "N"
                ],
                "purchaseItems" => [
                    [
                        "purchaseItemType" => "ticket",
                        "referenceNo" => $paymentObj['order_no'],
                        "purchaseItemDescription" => "Bundled insurance",
                        "purchaseItemPrice" => [
                            "amountText" => $paymentObj['amount'],
                            "currencyCode" => config('hbl.InputCurrency'),
                            "decimalPlaces" => 2,
                            "amount" => $paymentObj['amount']
                        ],
                        "subMerchantID" => "string",
                        "passengerSeqNo" => 1
                    ]
                ],
                "customFieldList" => $custom_fields
            ];

            $payload = [
                "request" => $request,
                "iss" => config('hbl.AccessToken'),
                "aud" => "PacoAudience",
                "CompanyApiKey" => config('hbl.AccessToken'),
                "iat" => $now->unix(),
                "nbf" => $now->unix(),
                "exp" => $now->addHour()->unix(),
            ];

            $stringPayload = json_encode($payload);
            $signingKey = $this->GetPrivateKey(config('hbl.MerchantSigningPrivateKey'));
            $encryptingKey = $this->GetPublicKey(config('hbl.PacoEncryptionPublicKey'));

            $body = $this->EncryptPayload($stringPayload, $signingKey, $encryptingKey);

            //third-party http client https://github.com/guzzle/guzzle
            $response = $this->client->post('api/1.0/Payment/prePaymentUi', [
                'headers' => [
                    'Accept' => 'application/jose',
                    'CompanyApiKey' => config('hbl.AccessToken'),
                    'Content-Type' => 'application/jose; charset=utf-8'
                ],
                'body' => $body
            ]);

            $token = $response->getBody()->getContents();
            $decryptingKey = $this->GetPrivateKey(config('hbl.MerchantDecryptionPrivateKey'));
            $signatureVerificationKey = $this->GetPublicKey(config('hbl.PacoSigningPublicKey'));

            return $this->DecryptToken($token, $decryptingKey, $signatureVerificationKey);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
