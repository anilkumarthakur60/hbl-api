<?php

namespace Anil\Hbl;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;

class TransactionStatus extends ActionRequest
{
    /**
     * @throws GuzzleException
     */
    public function Execute($orderNo): string
    {
        $response = $this->client->get('api/2.0/Inquiry/TransactionStatus', [
            'headers' => [
                'Accept' => 'application/json',
                'apiKey' => SecurityData::$AccessToken,
            ],
            'query' => ['orderNo' => $orderNo],
        ]);

        return $response->getBody()->getContents();
    }
}
