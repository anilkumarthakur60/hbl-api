<?php

namespace Anil\Hbl;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

class Refund extends ActionRequest
{
    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function executeJose(): string
    {
        $now = Carbon::now();
        $officeId = 9104137120;
        $orderNo = '1643362945100'; // OrderNo can be Refund one time only

        $actionBy = 'System|c88ef0dc-14ea-4556-922b-7f62a6a3ec9e';
        $actionEmail = 'babulal.cho@2c2pexternal.com';

        $request = [
            'refundAmount' => [
                'AmountText' => '000000100000',
                'CurrencyCode' => 'THB',
                'DecimalPlaces' => 2,
                'Amount' => 1000.00,
            ],
            'refundItems' => [],
            'localMakerChecker' => [
                'maker' => [
                    'username' => $actionBy,
                    'email' => $actionEmail,
                ],
            ],
            'officeId' => $officeId,
            'orderNo' => $orderNo,
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
        $response = $this->client->post('api/2.0/Refund/refund', [
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
