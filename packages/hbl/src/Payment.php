<?php

namespace Anil\Hbl;

use Illuminate\Support\Carbon;

class Payment extends ActionRequest
{
    public function executeFormJose(array $paymentObj = []): string
    {
        try {
            $now = Carbon::now();

            $custom_fields = [];
            if (! empty($paymentObj['custom_fields'])) {
                foreach ($paymentObj['custom_fields'] as $key => $value) {
                    $custom_fields[] = [
                        'fieldName' => $key,
                        'fieldValue' => $value,
                    ];
                }
            }

            $request = [
                'apiRequest' => [
                    'requestMessageID' => $this->Guid(),
                    'requestDateTime' => $now->utc()->format('Y-m-d\TH:i:s.v\Z'),
                    'language' => 'en-US',
                ],
                'officeId' => SecurityData::$MerchantId,
                'orderNo' => $paymentObj['order_no'],
                'productDescription' => 'Booking Payment',
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
                'request3dsFlag' => 'N',
                'transactionAmount' => [
                    'amountText' => str_pad(($paymentObj['amount'] == null ? 0 : $paymentObj['amount']) * 100, 12, '0', STR_PAD_LEFT),
                    'currencyCode' => 'USD',
                    'decimalPlaces' => 2,
                    'amount' => $paymentObj['amount'],
                ],
                'notificationURLs' => [
                    'confirmationURL' => $paymentObj['success_url'],
                    'failedURL' => $paymentObj['failed_url'],
                    'cancellationURL' => $paymentObj['cancel_url'],
                    'backendURL' => $paymentObj['backend_url'],
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
                        'referenceNo' => $paymentObj['order_no'],
                        'purchaseItemDescription' => 'Bundled insurance',
                        'purchaseItemPrice' => [
                            'amountText' => $paymentObj['amount'],
                            'currencyCode' => 'USD',
                            'decimalPlaces' => 2,
                            'amount' => $paymentObj['amount'],
                        ],
                        'subMerchantID' => 'string',
                        'passengerSeqNo' => 1,
                    ],
                ],
                'customFieldList' => $custom_fields,
            ];

            $payload = [
                'request' => $request,
                'iss' => SecurityData::$AccessToken,
                'aud' => 'PacoAudience',
                'CompanyApiKey' => SecurityData::$AccessToken,
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
                    'CompanyApiKey' => SecurityData::$AccessToken,
                    'Content-Type' => 'application/jose; charset=utf-8',
                ],
                'body' => $body,
            ]);

            $token = $response->getBody()->getContents();
            $decryptingKey = $this->GetPrivateKey(SecurityData::$MerchantDecryptionPrivateKey);
            $signatureVerificationKey = $this->GetPublicKey(SecurityData::$PacoSigningPublicKey);

            return $this->DecryptToken($token, $decryptingKey, $signatureVerificationKey);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
