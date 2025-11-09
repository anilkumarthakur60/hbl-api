<?php

namespace Anil\Hbl;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

class Settlement extends ActionRequest
{
    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function executeJose(string $orderNo): string
    {
        $now = Carbon::now();
        $officeId = 9104137120;
        $productDescription = 'Sample request for 1643362945100';

        $request = [
            'officeId' => $officeId,
            'orderNo' => $orderNo,
            'productDescription' => $productDescription,
            'issuerApprovalCode' => '141857', // approvalCode of order place (Payment api) response
            'actionBy' => 'System',
            'settlementAmount' => [
                'amountText' => '000000100000',
                'currencyCode' => 'NPR',
                'decimalPlaces' => 2,
                'amount' => 1000.00,
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

        $signingKey = $this->GetPrivateKey(config('hbl.merchant_signing_private_key'));
        $encryptingKey = $this->GetPublicKey(config('hbl.paco_encryption_public_key'));

        $body = $this->EncryptPayload($stringPayload, $signingKey, $encryptingKey);

        // third-party http client https://github.com/guzzle/guzzle
        $response = $this->client->put('api/2.0/Settlement', [
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
