<?php

namespace Anil\Hbl;

use Carbon\Carbon;

class Payment extends ActionRequest
{
    public function ExecuteFormJose($mid, $api_key, $curr, $amt, $threeD, $success_url, $failed_url, $cancel_url, $backend_url): string
    {
        $now = Carbon::now();
        $orderNo = $now->getPreciseTimestamp(3);
        $amt = round($amt, 2);
        $textAmount = str_pad(($amt == null ? 0 : $amt) * 100, 12, '0', STR_PAD_LEFT);

        $request = [
            'apiRequest' => [
                'requestMessageID' => $this->Guid(),
                'requestDateTime' => $now->utc()->format('Y-m-d\TH:i:s.v\Z'),
                'language' => 'en-US',
            ],
            'officeId' => config('hbl.OfficeId'),
            'orderNo' => $orderNo,
            'productDescription' => "desc for '$orderNo'",
            'paymentType' => 'CC',
            'paymentCategory' => 'ECOM',
            'storeCardDetails' => [
                'storeCardFlag' => 'N',
                'storedCardUniqueID' => $this->Guid(),
            ],
            'installmentPaymentDetails' => [
                'ippFlag' => 'N',
                'installmentPeriod' => 0,
                'interestType' => null,
            ],
            'mcpFlag' => 'N',
            'request3dsFlag' => config('hbl.Input3DS'),
            'transactionAmount' => [
                'amountText' => $textAmount,
                'currencyCode' => config('hbl.InputCurrency'),
                'decimalPlaces' => 2,
                'amount' => $amt,
            ],
            'notificationURLs' => [
                'confirmationURL' => $success_url,
                'failedURL' => $failed_url,
                'cancellationURL' => $cancel_url,
                'backendURL' => $backend_url,
            ],
            'deviceDetails' => [
                'browserIp' => '1.0.0.1',
                'browser' => 'Postman Browser',
                'browserUserAgent' => 'PostmanRuntime/7.26.8 - not from header',
                'mobileDeviceFlag' => 'N',
            ],
            'purchaseItems' => [
                [
                    'purchaseItemType' => 'ticket',
                    'referenceNo' => $orderNo,
                    'purchaseItemDescription' => 'Bundled insurance',
                    'purchaseItemPrice' => [
                        'amountText' => $textAmount,
                        'currencyCode' => config('hbl.InputCurrency'),
                        'decimalPlaces' => 2,
                        'amount' => $amt,
                    ],
                    'subMerchantID' => 'string',
                    'passengerSeqNo' => 1,
                ],
            ],
            'customFieldList' => [
                [
                    'fieldName' => 'TestField',
                    'fieldValue' => 'This is test',
                ],
            ],
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
}
