<?php

namespace Anil\Hbl\Contracts;

use Anil\Hbl\PaymentObject;

interface IHblPayment
{
    public static function pay(PaymentObject $paymentObject);
}
