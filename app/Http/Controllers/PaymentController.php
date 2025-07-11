<?php

namespace App\Http\Controllers;

use Anil\Hbl\Payment;
use Anil\Hbl\SecurityData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function store(Request $request)
    {

        try {
            $success_url = config('app.url').'/success';
            $failed_url = config('app.url').'/failed';
            $cancel_url = config('app.url').'/cancel';
            $backend_url = config('app.url').'/backend';
            $amount = 100;

            $payment = new Payment;
            $joseResponse = $payment->executeFormJose(
                mid: SecurityData::$MerchantId,
                api_key: SecurityData::$AccessToken,
                curr: 'NPR',
                amt: $amount,
                threeD: 'Y',
                success_url: $success_url,
                failed_url: $failed_url,
                cancel_url: $cancel_url,
                backend_url: $backend_url,
            );
            dd($joseResponse);
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
