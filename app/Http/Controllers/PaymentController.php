<?php

namespace App\Http\Controllers;

use Anil\Hbl\Payment;
use Anil\Hbl\TransactionStatus;
use App\Models\HblResponse;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class PaymentController extends Controller
{
    /**
     * @throws GuzzleException|Throwable
     */
    public function store(Request $request)
    {
        $payment = new Payment;
        $response = $payment->executeFormJose(
            [
                'order_no' => Str::random(15),
                'amount' => 1,
                'success_url' => route('payment.success'),
                'failed_url' => route('payment.failed'),
                'cancel_url' => route('payment.cancel'),
                'backend_url' => route('payment.backend'),
                'custom_fields' => [
                    'fullName' => 'Anil Kumar Thakur',
                    'email' => 'anilkumarthakur60@gmail.com',
                ],
            ]
        );

        $response = json_decode($response);

        return redirect()->away($response->response->data->paymentPage->paymentPageURL);
    }

    public function success(Request $request)
    {
        $response = HblResponse::firstOrCreate([
            'order_no' => $request->orderNo,
        ], [
            'response' => $request->all(),
            'status' => 'success',
        ]);
        $responses = HblResponse::query()->latest()->get();

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

        $responses = HblResponse::query()->latest()->get();

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

        $responses = HblResponse::query()->latest()->get();

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

        $responses = HblResponse::query()->latest()->get();

        return view('payment.index', compact('responses'));
    }

    public function index()
    {
        $responses = HblResponse::query()->latest()->get();

        return view('payment.index', compact('responses'));
    }

    /**
     * @throws GuzzleException
     */
    public function status($orderNo)
    {
        $hbl = new TransactionStatus;
        $response = $hbl->execute($orderNo);
        $response = json_decode($response);

        return response()->json($response);
    }

    public function delete($orderNo)
    {
        $response = HblResponse::where('order_no', $orderNo)->firstOrFail();
        $response->delete();

        return redirect()->route('payment.index')->with('success', 'Response deleted successfully');
    }
}
