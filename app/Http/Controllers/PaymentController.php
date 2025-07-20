<?php

namespace App\Http\Controllers;

use Anil\Hbl\Inquiry;
use Anil\Hbl\Payment;
use Anil\Hbl\Refund;
use Anil\Hbl\Settlement;
use Anil\Hbl\TransactionStatus;
use Anil\Hbl\VoidRequest;
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
            amount: 1,
            orderNo: Str::random(15),
            orderDescription: 'Booking Payment',
            purchaseItemType: 'ticket',
            additionalData: [
                'fullName' => 'Anil Kumar Thakur',
                'email' => 'anilkumarthakur60@gmail.com',
            ],
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

    public function refund()
    {
        $hbl = new Refund;
        $response = $hbl->executeJose();
        $response = json_decode($response);
        dd($response);

        return response()->json($response);
    }

    public function void($orderNo)
    {
        $hbl = new VoidRequest;
        $response = $hbl->executeJose();
        $response = json_decode($response);
        dd($response);
    }

    public function settlement($orderNo)
    {
        $hbl = new Settlement;
        $response = $hbl->executeJose();
        $response = json_decode($response);
        dd($response);
    }

    public function inquiry()
    {
        $hbl = new Inquiry;
        $response = $hbl->executeJose('p0xCk9eoxizYCDR');
        $response = json_decode($response);

        return response()->json($response);
    }
}
