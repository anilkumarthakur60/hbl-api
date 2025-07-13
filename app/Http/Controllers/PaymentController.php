<?php

namespace App\Http\Controllers;

use Anil\Hbl\Payment;
use Anil\Hbl\TransactionStatus;
use App\Models\HblResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function store(Request $request)
    {

        try {
            $amount = $request->amount ?? 1;

            $payment = new Payment;
            $joseResponse = $payment->executeFormJose(
                amt: $amount,
                additional_data: [
                    'fullname' => 'Anil Kumar Thakur',
                    'email' => 'anilkumarthakur60@gmail.com',
                    'contact_number' => '9843262634',
                    'amount' => $amount,
                ],
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

    public function status($orderNo)
    {
        $hbl = new TransactionStatus;
        $response = $hbl->Execute($orderNo);
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
