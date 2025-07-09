<?php

namespace Anil\Hbl;

class HblPayment
{
    public static function pay(PaymentObject $paymentObject)
    {
        $payment = new Payment;
        $joseResponse = $payment->ExecuteFormJose($paymentObject);
        $response_obj = json_decode($joseResponse);

        return $response_obj;
    }
}
