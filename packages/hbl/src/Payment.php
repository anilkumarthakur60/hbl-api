<?php

namespace Anil\Hbl;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

class Payment extends ActionRequest
{
    /**
     * @throws GuzzleException
     */
    public function Execute(): string
    {
        $now = Carbon::now();
        $orderNo = $now->getPreciseTimestamp(3);

        $request = [
            'apiRequest' => [
                'requestMessageID' => $this->Guid(),
                'requestDateTime' => $now->utc()->format('Y-m-d\TH:i:s.v\Z'),
                'language' => 'en-US',
            ],
            'officeId' => '9103332177',
            'orderNo' => $orderNo,
            'productDescription' => "desc for '$orderNo'",
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
                        'currencyCode' => 'THB',
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

        $stringRequest = json_encode($request);

        // third-party http client https://github.com/guzzle/guzzle
        $response = $this->client->post('api/2.0/Payment/prePaymentUi', [
            'headers' => [
                'Accept' => 'application/json',
                'apiKey' => SecurityData::$AccessToken,
                'Content-Type' => 'application/json; charset=utf-8',
            ],
            'body' => $stringRequest,
        ]);

        return $response->getBody()->getContents();
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function ExecuteJose(PaymentObject $paymentObject): string
    {
        $now = Carbon::now();
        $orderNo = $now->getPreciseTimestamp(3);

        $request = [
            'apiRequest' => [
                'requestMessageID' => $this->Guid(),
                'requestDateTime' => $now->utc()->format('Y-m-d\TH:i:s.v\Z'),
                'language' => 'en-US',
            ],
            'officeId' => 'DEMOOFFICE',
            'orderNo' => $orderNo,
            'productDescription' => "desc for '$orderNo'",
            'paymentType' => 'CC',
            'paymentCategory' => 'ECOM',
            'storeCardDetails' => [
                'storeCardFlag' => 'N',
                'storedCardUniqueID' => '{{guid}}',
            ],
            'installmentPaymentDetails' => [
                'ippFlag' => 'N',
                'installmentPeriod' => 0,
                'interestType' => null,
            ],
            'mcpFlag' => 'N',
            'request3dsFlag' => 'Y',
            'transactionAmount' => [
                'amountText' => '000000100000',
                'currencyCode' => 'THB',
                'decimalPlaces' => 2,
                'amount' => 1000,
            ],
            'notificationURLs' => [
                'confirmationURL' => $paymentObject->getSuccessUrl(),
                'failedURL' => $paymentObject->getFailedUrl(),
                'cancellationURL' => $paymentObject->getCancelUrl(),
                'backendURL' => $paymentObject->getBackendUrl(),
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
                        'currencyCode' => 'THB',
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
            'iss' => config('hbl.AccessToken'),
            'aud' => 'PacoAudience',
            'CompanyApiKey' => config('hbl.AccessToken'),
            'iat' => $now->unix(),
            'nbf' => $now->unix(),
            'exp' => $now->addHour()->unix(),
        ];

        dd(12);
        $stringPayload = json_encode($payload);
        $signingKey = $this->GetPrivateKey(config('hbl.MerchantSigningPrivateKey'));
        $encryptingKey = $this->GetPublicKey(config('hbl.PacoEncryptionPublicKey'));

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
        $decryptingKey = $this->GetPrivateKey(config('hbl.MerchantDecryptionPrivateKey'));
        $signatureVerificationKey = $this->GetPublicKey(config('hbl.PacoSigningPublicKey'));

        return $this->DecryptToken($token, $decryptingKey, $signatureVerificationKey);
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function ExecuteFormJose(PaymentObject $paymentObject): string
    {
        $now = Carbon::now();

        $request = [
            'apiRequest' => [
                'requestMessageID' => $this->Guid(),
                'requestDateTime' => $now->utc()->format('Y-m-d\TH:i:s.v\Z'),
                'language' => 'en-US',
            ],
            'officeId' => config('hbl.OfficeId'),
            'orderNo' => $paymentObject->getOrderNo(),
            'productDescription' => "desc for '{$paymentObject->getOrderNo()}'",
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
                'amountText' => str_pad((($paymentObject->getAmount() ?? 0) * 100), 12, '0', STR_PAD_LEFT),
                'currencyCode' => config('hbl.InputCurrency'),
                'decimalPlaces' => 2,
                'amount' => $paymentObject->getAmount(),
            ],
            'notificationURLs' => [
                'confirmationURL' => $paymentObject->getSuccessUrl(),
                'failedURL' => $paymentObject->getFailedUrl(),
                'cancellationURL' => $paymentObject->getCancelUrl(),
                'backendURL' => $paymentObject->getBackendUrl(),
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
                        'amountText' => '100',
                        'currencyCode' => 'NPR',
                        'decimalPlaces' => 2,
                        'amount' => 100,
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
        $signingKey = $this->GetPrivateKey(config('hbl.MerchantSigningPrivateKey'));
        $encryptingKey = $this->GetPublicKey(config('hbl.PacoEncryptionPublicKey'));

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

        dd($response);

        $token = $response->getBody()->getContents();
        $decryptingKey = $this->GetPrivateKey(config('hbl.MerchantDecryptionPrivateKey'));
        $signatureVerificationKey = $this->GetPublicKey(config('hbl.PacoSigningPublicKey'));

        return $this->DecryptToken($token, $decryptingKey, $signatureVerificationKey);
    }
}
