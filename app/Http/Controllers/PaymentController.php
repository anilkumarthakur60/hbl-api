<?php

namespace App\Http\Controllers;

use Anil\Hbl\Payment;
use Anil\Hbl\PaymentObject;
use Anil\Hbl\SecurityData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function store(Request $request)
    {

        try {
            $success_url = config('app.url').'/success';
            $failed_url = config('app.url').'/failed';
            $cancel_url = config('app.url').'/cancel';
            $backend_url = config('app.url').'/backend';
            $order_no = (string) Str::random(15);
            $amount = 100;

            $paymentObj = new PaymentObject(
                orderNo: $order_no,
                amount: $amount,
                successUrl: $success_url,
                failedUrl: $failed_url,
                cancelUrl: $cancel_url,
                backendUrl: $backend_url,
                customFields: [
                    'refId' => (string) Str::random(15),
                ]
            );

            $payment = new Payment;
            $joseResponse = $payment->ExecuteFormJose(
                mid: SecurityData::$MerchantId,
                api_key: SecurityData::$AccessToken,
                curr: 'NPR',
                amt: $paymentObj->amount,
                threeD: 'Y',
                success_url: $paymentObj->successUrl,
                failed_url: $paymentObj->failedUrl,
                cancel_url: $paymentObj->cancelUrl,
                backend_url: $paymentObj->backendUrl,
            );
            $response = json_decode($joseResponse);

            return redirect()->away($response->response->data->paymentPage->paymentPageURL);
        } catch (\Exception $e) {
            Log::error($e);
            dd($e);
        }
    }

    public function success(Request $request)
    {
        dd($request->all());
    }

    public function failed(Request $request)
    {
        dd($request->all());
    }

    public function cancel(Request $request)
    {
        dd($request->all());
    }

    public function backend(Request $request)
    {
        dd($request->all());
    }
}
