<?php

use Anil\Hbl\Payment;
use Anil\Hbl\SecurityData;
use Illuminate\Support\Str;

beforeEach(/**
 * @throws Exception
 */ function () {
    $this->merchantId = SecurityData::$MerchantId;
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
        [
            'order_no' => Str::random(15),
            'amount' => 1,
            'success_url' => config('app.url').'/success',
            'failed_url' => config('app.url').'/failed',
            'cancel_url' => config('app.url').'/cancel',
            'backend_url' => config('app.url').'/backend',
            'custom_fields' => [
                'fullname' => 'Anil Kumar Thakur',
                'email' => 'anilkumarthakur60@gmail.com',
            ],
        ]
    );

    dd($json);
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
