<?php

namespace Anil\Hbl\Contracts;

use Anil\Hbl\Dto\PaymentDto;

interface HblPayment
{
    public function createPayment(PaymentDto $paymentDto): PaymentDto;

    public function getPayment(string $paymentId): PaymentDto;

    public function updatePayment(string $paymentId, PaymentDto $paymentDto): PaymentDto;

    public function deletePayment(string $paymentId): void;

    public function getPaymentList(array $data): array;
}
