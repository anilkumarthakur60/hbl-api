<?php

namespace App\Http\Controllers;

use Anil\Hbl\Payment;
use Anil\Hbl\PaymentObject;
use Illuminate\Http\Request;
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

            $paymentObj = new PaymentObject;
            $paymentObj->setOrderNo($order_no);
            $paymentObj->setAmount($amount);
            $paymentObj->setSuccessUrl($success_url);
            $paymentObj->setCancelUrl($cancel_url);
            $paymentObj->setBackendUrl($backend_url);
            $paymentObj->setFailedUrl($failed_url);
            $paymentObj->setCustomFields([
                'refId' => (string) Str::random(15),
            ]);
            $payment = new Payment;
            $joseResponse = $payment->ExecuteFormJose($paymentObj);
            $response = json_decode($joseResponse);

            return redirect($response->response->data->paymentPage->paymentPageURL);
        } catch (\Exception $e) {
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
