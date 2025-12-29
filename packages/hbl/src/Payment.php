<?php

namespace Anil\Hbl;

use Illuminate\Support\Carbon;

class Payment extends ActionRequest
{
    public function executeFormJose(float $amount, string $orderNo): string
    {
        $amountText = str_pad(($amount) * 100, 12, '0', STR_PAD_LEFT);
        try {
            $now = Carbon::now();
            $request = [
                'apiRequest' => [
                    'requestMessageID' => $this->guid(),
                    'requestDateTime' => $now->utc()->format('Y-m-d\TH:i:s.v\Z'),
                    'language' => 'en-US',
                ],
                'officeId' => SecurityData::$MerchantId,
                'orderNo' => $orderNo,
                'productDescription' => "Payment for '$orderNo'",
                'storeCardDetails' => [
                    'storeCardFlag' => 'N',
                    'storedCardUniqueID' => '{{guid}}',
                ],
                'paymentType' => 'CC',
                'paymentCategory' => 'ECOM',
                'installmentPaymentDetails' => [
                    'ippFlag' => 'N',
                    'installmentPeriod' => 0,
                    'interestType' => null,
                ],
                'mcpFlag' => 'N',
                'request3dsFlag' => 'Y',
                'transactionAmount' => [
                    'amountText' => $amountText,
                    'currencyCode' => 'USD',
                    'decimalPlaces' => 2,
                    'amount' => $amount,
                ],
                'notificationURLs' => [
                    'confirmationURL' => route('payment.success'),
                    'failedURL' => route('payment.failed'),
                    'cancellationURL' => route('payment.cancel'),
                    'backendURL' => route('payment.backend'),
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
                        'referenceNo' => '2322460376026',
                        'purchaseItemDescription' => 'Bundled insurance',
                        'purchaseItemPrice' => [
                            'amountText' => '000000000100',
                            'currencyCode' => 'NPR',
                            'decimalPlaces' => 2,
                            'amount' => 1,
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
                'iss' => SecurityData::$AccessToken,
                'aud' => 'PacoAudience',
                'CompanyApiKey' => SecurityData::$AccessToken,
                'iat' => $now->unix(),
                'nbf' => $now->unix(),
                'exp' => $now->addHour()->unix(),
            ];

            $stringPayload = json_encode($payload);
            $signingKey = $this->getPrivateKey(SecurityData::$MerchantSigningPrivateKey);
            $encryptingKey = $this->getPublicKey(SecurityData::$PacoEncryptionPublicKey);

            $body = $this->encryptPayload($stringPayload, $signingKey, $encryptingKey);
            $response = $this->client->post('api/2.0/Payment/prePaymentUi', [
                'headers' => [
                    'Accept' => 'application/jose',
                    'CompanyApiKey' => SecurityData::$AccessToken,
                    'Content-Type' => 'application/jose; charset=utf-8',
                ],
                'body' => $body,
            ]);

            $token = $response->getBody()->getContents();
            $decryptingKey = $this->getPrivateKey(SecurityData::$MerchantDecryptionPrivateKey);
            $signatureVerificationKey = $this->getPublicKey(SecurityData::$PacoSigningPublicKey);

            return $this->decryptToken($token, $decryptingKey, $signatureVerificationKey);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
