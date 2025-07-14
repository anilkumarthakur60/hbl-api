<?php

namespace Anil\Hbl;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;

class Payment extends ActionRequest
{
    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function executeFormJose($amt, $orderNo, $orderDescription, $additionalData): string
    {
        $now = Carbon::now();

        $request = [
            'apiRequest' => [
                'requestMessageID' => $this->Guid(),
                'requestDateTime' => $now->utc()->format('Y-m-d\TH:i:s.v\Z'),
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
                'amountText' => str_pad(($amt == null ? 0 : $amt) * 100, 12, '0', STR_PAD_LEFT),
                'currencyCode' => config('hbl.InputCurrency'),
                'decimalPlaces' => config('hbl.decimal_places'),
                'amount' => $amt,
            ],
            'notificationURLs' => [
                'confirmationURL' => config('hbl.redirect_url.success'),
                'failedURL' => config('hbl.redirect_url.failed'),
                'cancellationURL' => config('hbl.redirect_url.cancel'),
                'backendURL' => config('hbl.redirect_url.backend'),
            ],
            'deviceDetails' => config('hbl.device_details'),
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
            'customFieldList' => $additionalData,
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
                'CompanyApiKey' => SecurityData::$AccessToken,
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
