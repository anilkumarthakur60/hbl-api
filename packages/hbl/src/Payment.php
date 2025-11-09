<?php

namespace Anil\Hbl;

use Illuminate\Support\Carbon;

class Payment extends ActionRequest
{
    public function executeFormJose(float $amount, string $orderNo): string
    {
        $amount = round($amount, 2);
        try {
            $now = Carbon::now();
            $request = [
                'apiRequest' => [
                    'requestMessageID' => $this->Guid(),
                    'requestDateTime' => $now->utc()->format('Y-m-d\TH:i:s.v\Z'),
                    'language' => 'en-US',
                ],
                'officeId' => SecurityData::$MerchantId,
                'orderNo' => $orderNo,
                'productDescription' => "Booking Payment Test for $orderNo",
                'paymentType' => 'CC',
                'paymentCategory' => 'ECOM',
                'creditCardDetails' => [
                    'cardNumber' => '4706860000002325',
                    'cardExpiryMMYY' => '1225',
                    'cvvCode' => '761',
                    'payerName' => 'Demo Sample',
                ],
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
                    'amountText' => '000000100000',
                    'currencyCode' => 'NPR',
                    'decimalPlaces' => 2,
                    'amount' => 1000,
                ],
                'notificationURLs' => [
                    'confirmationURL' => 'http://example-confirmation.com',
                    'failedURL' => 'http://example-failed.com',
                    'cancellationURL' => 'http://example-cancellation.com',
                    'backendURL' => 'http://example-backend.com',
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
                            'amountText' => '000000100000',
                            'currencyCode' => 'NPR',
                            'decimalPlaces' => 2,
                            'amount' => 1000,
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
            $signingKey = $this->GetPrivateKey(SecurityData::$MerchantSigningPrivateKey);
            $encryptingKey = $this->GetPublicKey(SecurityData::$PacoEncryptionPublicKey);

            $body = $this->EncryptPayload($stringPayload, $signingKey, $encryptingKey);
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
