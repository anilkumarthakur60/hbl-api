<?php

namespace Anil\Hbl;

use Illuminate\Contracts\Support\Arrayable;

class PaymentObject implements Arrayable
{
    public function __construct(
        public readonly string $orderNo,
        public readonly float $amount,
        public readonly string $successUrl,
        public readonly string $failedUrl,
        public readonly string $cancelUrl,
        public readonly string $backendUrl,
        public readonly array $customFields = []
    ) {}

    public function toArray(): array
    {
        return [
            'order_no' => $this->orderNo,
            'amount' => $this->amount,
            'success_url' => $this->successUrl,
            'failed_url' => $this->failedUrl,
            'cancel_url' => $this->cancelUrl,
            'backend_url' => $this->backendUrl,
            'custom_fields' => $this->customFields,
        ];
    }
}
