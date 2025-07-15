<?php

use Anil\Hbl\Payment;
use Anil\Hbl\SecurityData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

// beforeEach(function () {
//     $this->amount      = 100;
//     $this->successUrl  = config('app.url') . '/success';
//     $this->failedUrl   = config('app.url') . '/failed';
//     $this->cancelUrl   = config('app.url') . '/cancel';
//     $this->backendUrl  = config('app.url') . '/backend';

//     // A fake HBL API payload
//     $this->fakeApiResponse = [
//         'paymentIncompleteResult' => [
//             'orderNo'                => 'TEST123',
//             'transactionDateTime'    => '2025-07-11T01:58:49.9319643Z',
//             'paymentExpiryDateTime'  => '2025-07-11T02:43:49.9319643Z',
//             'availablePaymentTypes'  => ['CC', 'CC-VI', 'CC-CA', 'CC-AX', 'CC-UP'],
//             'currencyConversionType' => 'None',
//             'transactionAmount'      => [
//                 'amountText'     => '000000010000',
//                 'currencyCode'   => 'NPR',
//                 'decimalPlaces'  => 2,
//                 'amount'         => 100.0,
//             ],
//             'paymentStatusInfo' => [
//                 'paymentStatus' => 'PCPS',
//                 'paymentStep'   => 'GP',
//             ],
//         ],
//         'paymentPage' => [
//             'paymentPageURL'     => 'https://payment.demo-paco.2c2p.com/payment/?pid=TEST123&lang=en-US',
//             'validTillDateTime'  => '2025-07-11T02:43:49',
//         ],
//         'version'     => '2.0',
//         'apiResponse' => [
//             'responseCode'        => 'PC-B050001',
//             'responseDescription' => 'Payment is pending',
//             'marketingDescription' => 'Payment is pending. Please complete your payment through the selected payment channel.',
//         ],
//     ];

//     // Stub every HTTP call to return our fake payload
//     Http::fake([
//         'payment.demo-paco.2c2p.com/*' => Http::response(
//             json_encode($this->fakeApiResponse),
//             200,
//             ['Content-Type' => 'application/json']
//         ),
//     ]);

//     $this->payment = new Payment;
// });

// it('sends the correct HTTP request and returns a well-formed JSON response', function () {
//     $json = $this->payment->executeFormJose(
//         mid: SecurityData::$MerchantId,
//         api_key: SecurityData::$AccessToken,
//         curr: 'NPR',
//         amt: $this->amount,
//         threeD: 'Y',
//         success_url: $this->successUrl,
//         failed_url: $this->failedUrl,
//         cancel_url: $this->cancelUrl,
//         backend_url: $this->backendUrl,
//     );

//     // 1) Confirm we hit the expected endpoint with POST
//     Http::assertSent(
//         fn($req) =>
//         $req->method() === 'POST'
//             && str_contains($req->url(), 'payment.demo-paco.2c2p.com/payment')
//             && $req['mid'] === SecurityData::$MerchantId
//             && $req['amt'] === $this->amount
//     );

//     // 2) Assert we got back a JSON string
//     expect($json)->toBeString();

//     // 3) Decode and verify the full structure
//     $response = json_decode($json);

//     expect($response)->toBeObject();

//     // — paymentIncompleteResult
//     expect($response->paymentIncompleteResult)->toBeObject();
//     expect($response->paymentIncompleteResult->orderNo)->toBe('TEST123');
//     expect($response->paymentIncompleteResult->transactionAmount->amount)->toBe(100.0);
//     expect($response->paymentIncompleteResult->transactionAmount->currencyCode)->toBe('NPR');
//     expect($response->paymentIncompleteResult->availablePaymentTypes)
//         ->toBeArray()
//         ->toHaveCount(5)
//         ->toContain('CC')
//         ->toContain('CC-AX');
//     expect($response->paymentIncompleteResult->paymentStatusInfo->paymentStatus)
//         ->toBe('PCPS');

//     // — paymentPage
//     expect($response->paymentPage->paymentPageURL)
//         ->toStartWith('https://payment.demo-paco.2c2p.com/payment/');
//     expect($response->paymentPage->validTillDateTime)
//         ->toMatch('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');

//     // — apiResponse
//     expect($response->apiResponse->responseCode)->toBe('PC-B050001');
//     expect($response->apiResponse->responseDescription)->toBe('Payment is pending');
// });

// it('throws an exception when the HBL API returns a non-200 status', function () {
//     // Override fake to simulate server error
//     Http::fake(['*' => Http::response('Server Error', 500)]);

//     expect(fn() => $this->payment->executeFormJose(
//         mid: SecurityData::$MerchantId,
//         api_key: SecurityData::$AccessToken,
//         curr: 'NPR',
//         amt: $this->amount,
//         threeD: 'Y',
//         success_url: $this->successUrl,
//         failed_url: $this->failedUrl,
//         cancel_url: $this->cancelUrl,
//         backend_url: $this->backendUrl,
//     ))->toThrow(Exception::class);
// });
beforeEach(function () {
    $this->merchantId = config('hbl.OfficeId');
    $this->apiKey = config('hbl.AccessToken');
    $this->baseUrl = config('hbl.EndPoint');
    if (! $this->merchantId || ! $this->apiKey || ! $this->baseUrl) {
        throw new Exception('HBL integration credentials or URL not configured in .env');
    }
    $this->payment = new Payment;
    $appUrl = rtrim(config('app.url'), '/');
    $this->successUrl = "{$appUrl}/success";
    $this->failedUrl = "{$appUrl}/failed";
    $this->cancelUrl = "{$appUrl}/cancel";
    $this->backendUrl = "{$appUrl}/backend";
});

it('actually hits the HBL sandbox and returns a well-formed response', function () {
    $json = $this->payment->executeFormJose(
        amt: 100,
        orderNo: Str::random(15),
        orderDescription: 'Test Payment',
        additional_data: [
            'fullname' => 'Anil Kumar Thakur',
            'email' => 'anilkumarthakur60@gmail.com',
        ],
        purchaseItems: [
            'purchaseItemType' => 'ticket',
        ],
    );
    $object = json_decode($json);

    // root / data sanity
    expect($object)->toBeObject()
        ->and($object->response)->toBeObject()
        ->and($object->response->data)->toBeObject();

    // pull out the two main bits
    $result = $object->response->data->paymentIncompleteResult;
    $page = $object->response->data->paymentPage;
    $api = $object->response->apiResponse;

    // ─── paymentIncompleteResult ───────────────────────────────────────────
    expect($result)->toBeObject()
        ->and($result->notificationURLs)->toBeObject()
        ->and($result->notificationURLs->confirmationUrl)->toBeString()
        ->and($result->notificationURLs->cancellationUrl)->toBeString()
        ->and($result->notificationURLs->failedUrl)->toBeString()
        ->and($result->notificationURLs->backendUrl)->toBeString()
        ->and($result->availablePaymentTypes)->toBeArray();

    foreach (['CC', 'CC-VI', 'CC-CA', 'CC-AX', 'CC-UP'] as $type) {
        expect($result->availablePaymentTypes)->toContain($type);
    }

    // status info
    expect($result->paymentStatusInfo)->toBeObject()
        ->and($result->paymentStatusInfo->paymentStatus)->toBeString()
        ->and($result->paymentStatusInfo->paymentStep)->toBeString()
        ->and($result->paymentStatusInfo->lastUpdatedDateTime)->toBeString()
        ->and($result->orderNo)->toBeString()
        ->and($result->productDescription)->toBeString()
        ->and($result->paymentExpiryDateTime)->toBeString()
        ->and($result->currencyConversionType)->toBeString()
        ->and($result->transactionAmount)->toBeObject()
        ->and($result->transactionAmount->amount)->toBeFloat()
        ->and($result->transactionAmount->currencyCode)->toBeString()
        ->and($result->transactionAmount->decimalPlaces)->toBeInt()
        ->and($result->transactionAmount->amountText)->toBeString()
        ->and($page)->toBeObject()
        ->and($page->paymentPageURL)->toBeString()
        ->and($page->validTillDateTime)->toBeString()
        ->and($api->responseMessageId)->toBeString()
        ->and($api->responseToRequestMessageId)->toBeString()
        ->and($api->responseCode)->toBeString()
        ->and($api->responseDescription)->toBeString()
        ->and($api->responseDateTime)->toBeString()
        ->and($api->responseTime)->toBeInt()
        ->and($api->marketingDescription)->toBeString()
        ->and(is_string($api->acquirerResponseCode) || is_null($api->acquirerResponseCode))->toBeTrue()
        ->and(is_string($api->acquirerResponseDescription) || is_null($api->acquirerResponseDescription))->toBeTrue()
        ->and(is_string($api->eciValue) || is_null($api->eciValue))->toBeTrue()
        ->and($object->aud)->toBeString()
        ->and($object->iss)->toBeString()
        ->and($object->exp)->toBeInt()
        ->and($object->iat)->toBeInt()
        ->and($object->nbf)->toBeInt();

    // core fields

    // amount

    // ─── paymentPage ───────────────────────────────────────────────────────

    // ─── apiResponse ───────────────────────────────────────────────────────

    // these three may be string or null

    // ─── JWT Claims ────────────────────────────────────────────────────────

    // ─── OPTIONAL / NULLABLE FIELDS IN paymentIncompleteResult ────────────
    foreach (
        [
            // string|null fields
            'channelCode',
            'agentCode',
            'currencyConversionMerchantId',
            'aresACSChallenge',
            'failedReason',
            'invoiceNo2C2P',
            'pspInvoiceNo',
            'pspReferenceNo',
            'settlementAmount',
            'ddcId',
            'clientIp',
            // array|null fields
            'untokenizedStoredCardList',
            'preferredPaymentTypes',
            'customFieldList',
            // userDefined1–10 (string|null)
            'userDefined1',
            'userDefined2',
            'userDefined3',
            'userDefined4',
            'userDefined5',
            'userDefined6',
            'userDefined7',
            'userDefined8',
            'userDefined9',
            'userDefined10',
        ] as $field
    ) {
        $val = $result->{$field};
        if (in_array($field, [
            'untokenizedStoredCardList',
            'preferredPaymentTypes',
            'customFieldList',
        ])) {
            expect(is_array($val) || is_null($val))->toBeTrue();
        } else {
            expect(is_string($val) || is_null($val))->toBeTrue();
        }
    }

    // officeId must always be string
    expect($result->officeId)->toBeString();
});
