<?php

namespace Anil\Hbl;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

class VoidRequest extends ActionRequest
{
    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function executeJose(string $orderNo, string $productDescription, string $issuerApprovalCode, string $amountText, float $amount): string
    {
        $now = Carbon::now();

        $request = [
            'officeId' => config('hbl.merchant_id'),
            'orderNo' => $orderNo,
            'productDescription' => $productDescription,
            'issuerApprovalCode' => $issuerApprovalCode,
            'actionBy' => 'System',
            'voidAmount' => [
                'amountText' => $amountText,
                'currencyCode' => config('hbl.currency_code'),
                'decimalPlaces' => config('hbl.decimal_places'),
                'amount' => $amount,
            ],
        ];

        $payload = [
            'request' => $request,
            'iss' => config('hbl.access_token'),
            'aud' => config('hbl.aud'),
            'CompanyApiKey' => config('hbl.access_token'),
            'iat' => $now->unix(),
            'nbf' => $now->unix(),
            'exp' => $now->addHour()->unix(),
        ];

        $stringPayload = json_encode($payload);
        $signingKey = $this->GetPrivateKey(config('hbl.merchant_signing_private_key'));
        $encryptingKey = $this->GetPublicKey(config('hbl.paco_encryption_public_key'));

        $body = $this->EncryptPayload($stringPayload, $signingKey, $encryptingKey);

        // third-party http client https://github.com/guzzle/guzzle
        $response = $this->client->post('api/2.0/Void', [
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
