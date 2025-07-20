<?php

namespace Anil\Hbl;

use Illuminate\Support\Carbon;

class Payment extends ActionRequest
{
    public function executeFormJose(float $amount, string $orderNo, string $orderDescription, string $purchaseItemType = 'ticket', array $additionalData = []): string
    {
        $amount = round($amount, 2);
        $strAmount = str_pad(($amount == null ? 0 : $amount) * 100, 12, '0', STR_PAD_LEFT);
        try {
            $now = Carbon::now();

            $custom_fields = [];
            if (! empty($additionalData['custom_fields'])) {
                foreach ($additionalData['custom_fields'] as $key => $value) {
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
                    'language' => config('hbl.language'),
                ],
                'officeId' => SecurityData::$MerchantId,
                'orderNo' => $orderNo,
                'productDescription' => $orderDescription,
                'paymentType' => config('hbl.payment_type'),
                'paymentCategory' => config('hbl.payment_category'),
                'storeCardDetails' => [
                    'storeCardFlag' => config('hbl.store_card_flag'),
                    'storedCardUniqueID' => $this->Guid(),
                ],
                'installmentPaymentDetails' => [
                    'ippFlag' => config('hbl.ipp_flag'),
                    'installmentPeriod' => config('hbl.installment_period'),
                    'interestType' => null,
                ],
                'mcpFlag' => config('hbl.mcp_flag'),
                'request3dsFlag' => config('hbl.request_3ds_flag'),
                'transactionAmount' => [
                    'amountText' => $strAmount,
                    'currencyCode' => config('hbl.currency_code'),
                    'decimalPlaces' => 2,
                    'amount' => $amount,
                ],
                'notificationURLs' => [
                    'confirmationURL' => config('hbl.redirect_urls.confirmation'),
                    'failedURL' => config('hbl.redirect_urls.failed'),
                    'cancellationURL' => config('hbl.redirect_urls.cancel'),
                    'backendURL' => config('hbl.redirect_urls.backend'),
                ],
                'deviceDetails' => [
                    'browserIp' => '1.0.0.1',
                    'browser' => 'Postman Browser',
                    'browserUserAgent' => 'PostmanRuntime/7.26.8 - not from header',
                    'mobileDeviceFlag' => 'N',
                ],
                'purchaseItems' => [
                    [
                        'purchaseItemType' => $purchaseItemType,
                        'referenceNo' => $orderNo,
                        'purchaseItemDescription' => $orderDescription,
                        'purchaseItemPrice' => [
                            'amountText' => $strAmount,
                            'currencyCode' => config('hbl.currency_code'),
                            'decimalPlaces' => 2,
                            'amount' => $amount,
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
                'aud' => config('hbl.aud'),
                'CompanyApiKey' => SecurityData::$AccessToken,
                'iat' => $now->unix(),
                'nbf' => $now->unix(),
                'exp' => $now->addHour()->unix(),
            ];

            $stringPayload = json_encode($payload);
            $signingKey = $this->GetPrivateKey(config('hbl.merchant_signing_private_key'));
            $encryptingKey = $this->GetPublicKey(config('hbl.paco_encryption_public_key'));

            $body = $this->EncryptPayload($stringPayload, $signingKey, $encryptingKey);

            // third-party http client https://github.com/guzzle/guzzle
            $response = $this->client->post('api/2.0/Payment/prePaymentUi', [
                'headers' => [
                    'Accept' => 'application/jose',
                    'CompanyApiKey' => config('hbl.access_token'),
                    'Content-Type' => 'application/jose; charset=utf-8',
                ],
                'body' => $body,
            ]);

            $token = $response->getBody()->getContents();
            $decryptingKey = $this->GetPrivateKey(config('hbl.merchant_decryption_private_key'));
            $signatureVerificationKey = $this->GetPublicKey(config('hbl.paco_signing_public_key'));

            return $this->DecryptToken($token, $decryptingKey, $signatureVerificationKey);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
