<?php

namespace Anil\Hbl;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;

class Inquiry extends ActionRequest
{
    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function executeJose(?string $orderNo = null, ?string $invoiceNo2C2P = null, ?string $fromDate = null, ?string $toDate = null, ?string $amountFrom = null, ?string $amountTo = null, ?string $controllerInternalID = null): string
    {
        $now = Carbon::now();

        $officeId = 9104137120;
        $orderNo = 'p0xCk9eoxizYCDR';

        $request = [
            'apiRequest' => [
                'requestMessageID' => $this->Guid(),
                'requestDateTime' => $now->utc()->format('Y-m-d\TH:i:s.v\Z'),
                'language' => 'en-US',
            ],
            'advSearchParams' => [
                'controllerInternalID' => null,
                'officeId' => [
                    $officeId,
                ],
                'orderNo' => [
                    $orderNo,
                ],
                'invoiceNo2C2P' => null,
                'fromDate' => '0001-01-01T00:00:00',
                'toDate' => '0001-01-01T00:00:00',
                'amountFrom' => null,
                'amountTo' => null,
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

        // third-party http client https://github.com/guzzle/guzzle
        $response = $this->client->post('api/2.0/Inquiry/TransactionList', [
            'headers' => [
                'Accept' => 'application/jose',
                'CompanyApiKey' => SecurityData::$AccessToken,
                'Content-Type' => 'application/jose; charset=utf-8',
            ],
            'body' => $body,
        ]);

        $token = $response->getBody()->getContents();
        $decryptingKey = $this->GetPrivateKey(config('hbl.merchant_decryption_private_key'));
        $signatureVerificationKey = $this->GetPublicKey(config('hbl.paco_signing_public_key'));

        return $this->DecryptToken($token, $decryptingKey, $signatureVerificationKey);
    }
}
