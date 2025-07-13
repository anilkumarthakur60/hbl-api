<?php

namespace App\Http\Controllers;

use Anil\Hbl\Payment;
use Anil\Hbl\SecurityData;
use App\Models\HblResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController1 extends Controller
{
    public function store(Request $request)
    {

        try {
            $success_url = config('app.url') . '/success';
            $failed_url = config('app.url') . '/failed';
            $cancel_url = config('app.url') . '/cancel';
            $backend_url = config('app.url') . '/backend';
            $amount = $request->amount ?? 1;

            $payment = new Payment;
            $joseResponse = $payment->ExecuteFormJose(
                mid: config('hbl.OfficeId'),
                api_key: config('hbl.AccessToken'),
                curr: 'NPR',
                amt: $amount,
                threeD: 'Y',
                success_url: $success_url,
                failed_url: $failed_url,
                cancel_url: $cancel_url,
                backend_url: $backend_url,
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
        $response = HblResponse::firstOrCreate([
            'order_no' => $request->orderNo,
        ], [
            'response' => $request->all(),
            'status' => 'success',
        ]);
        $responses = HblResponse::all();

        return view('payment.index', compact('responses'));
    }

    public function failed(Request $request)
    {
        $response = HblResponse::firstOrCreate([
            'order_no' => $request->orderNo,
        ], [
            'response' => $request->all(),
            'status' => 'failed',
        ]);

        $responses = HblResponse::all();
        return view('payment.index', compact('responses'));
    }

    public function cancel(Request $request)
    {
        $response = HblResponse::firstOrCreate([
            'order_no' => $request->orderNo,
        ], [
            'response' => $request->all(),
            'status' => 'cancel',
        ]);

        $responses = HblResponse::all();
        return view('payment.index', compact('responses'));
    }

    public function backend(Request $request)
    {
        $response = HblResponse::firstOrCreate([
            'order_no' => $request->orderNo,
        ], [
            'response' => $request->all(),
            'status' => 'backend',
        ]);

        $responses = HblResponse::all();
        return view('payment.index', compact('responses'));
    }

    public function index()
    {
        $responses = HblResponse::all();

        return view('payment.index', compact('responses'));
    }
}
