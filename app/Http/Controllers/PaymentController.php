<?php

namespace App\Http\Controllers;

use Anil\Hbl\Dto\PaymentDto;
use Anil\Hbl\HblPayment;
use Anil\Hbl\PaymentObject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        // $paymentObj = new PaymentDto();
        // $paymentObj->setOrderNo(Str::uuid());
        // $paymentObj->setAmount(100);
        // $paymentObj->setSuccessUrl("http://127.0.0.1:8000/success");
        // $paymentObj->setCancelUrl("http://127.0.0.1:8000/cancel");
        // $paymentObj->setBackendUrl("http://127.0.0.1:8000/backend");
        // $paymentObj->setFailedUrl("http://127.0.0.1:8000/failed");
        // $paymentObj->setCustomFields([
        //     "refId" => Str::uuid()
        // ]);
        // // dd($paymentObj->toArray());
        // $payment = HblPayment::createPayment($paymentObj);
        // dd($payment);

        $success_url = config('app.url').'/success';
        $failed_url = config('app.url').'/failed';
        $cancel_url = config('app.url').'/cancel';
        $backend_url = config('app.url').'/backend';
        $order_no = (string) Str::uuid();
        $amount = 100;

        $paymentObj = new PaymentObject;
        $paymentObj->setOrderNo($order_no);
        $paymentObj->setAmount($amount);
        $paymentObj->setSuccessUrl($success_url);
        $paymentObj->setCancelUrl($cancel_url);
        $paymentObj->setBackendUrl($backend_url);
        $paymentObj->setFailedUrl($failed_url);
        $paymentObj->setCustomFields([
            'refId' => (string) Str::uuid(),
        ]);
        $response = HblPayment::pay($paymentObj);

        return redirect($response->response->data->paymentPage->paymentPageURL);
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
