<?php

use Anil\Hbl\Payment;
use Anil\Hbl\SecurityData;
use Illuminate\Support\Facades\Http;

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
        mid: $this->merchantId,
        api_key: $this->apiKey,
        curr: 'NPR',
        amt: 100,
        threeD: 'Y',
        success_url: $this->successUrl,
        failed_url: $this->failedUrl,
        cancel_url: $this->cancelUrl,
        backend_url: $this->backendUrl,
    );

    // 1) We got some JSON back
    expect($json)->toBeString();

    $response = json_decode($json);
    expect(json_last_error())->toBe(JSON_ERROR_NONE);
    expect($response)->toBeObject();

    // 2) paymentIncompleteResult shape
    expect($response)->toHaveProperty('paymentIncompleteResult');
    $pi = $response->paymentIncompleteResult;
    expect($pi)->toBeObject();
    expect($pi)->toHaveProperty('orderNo')->not->toBeEmpty();
    expect($pi)->toHaveProperty('transactionDateTime')
        ->toMatch('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
    expect($pi)->toHaveProperty('availablePaymentTypes')
        ->toBeArray()
        ->not->toBeEmpty();

    // 3) paymentPage shape
    expect($response)->toHaveProperty('paymentPage');
    $pp = $response->paymentPage;
    expect($pp)->toHaveProperty('paymentPageURL')
        ->toStartWith($this->baseUrl);
    expect($pp)->toHaveProperty('validTillDateTime')
        ->toMatch('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');

    // 4) apiResponse shape
    expect($response)->toHaveProperty('apiResponse');
    $ar = $response->apiResponse;
    expect($ar)->toHaveProperty('responseCode')->not->toBeEmpty();
    expect($ar)->toHaveProperty('responseDescription')->not->toBeEmpty();
});
