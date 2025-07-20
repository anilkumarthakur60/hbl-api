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

        $request = [
            'apiRequest' => [
                'requestMessageID' => $this->Guid(),
                'requestDateTime' => $now->utc()->format('Y-m-d\TH:i:s.v\Z'),
                'language' => 'en-US',
            ],
            'advSearchParams' => [
                'controllerInternalID' => $controllerInternalID,
                'officeId' => [
                    $officeId,
                ],
                'orderNo' => [
                    $orderNo,
                ],
                'invoiceNo2C2P' => $invoiceNo2C2P,
                'amountFrom' => $amountFrom,
                'amountTo' => $amountTo,
            ],
        ];

        if ($fromDate) {
            $request['advSearchParams']['fromDate'] = $fromDate;
        }
        if ($toDate) {
            $request['advSearchParams']['toDate'] = $toDate;
        }

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
        $signingKey = $this->GetPrivateKey(config('hbl.merchant_signing_private_key'));
        $encryptingKey = $this->GetPublicKey(config('hbl.paco_encryption_public_key'));

        $body = $this->EncryptPayload($stringPayload, $signingKey, $encryptingKey);

        // third-party http client https://github.com/guzzle/guzzle
        $response = $this->client->post('api/2.0/Inquiry/TransactionList', [
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
    }
}
