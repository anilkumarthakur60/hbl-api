<?php

declare(strict_types=1);

namespace Anil\Hbl\Dto;

use JsonSerializable;

/**
 * Data Transfer Object for initiating a payment request.
 *
 * All properties are immutable once the object is constructed.
 */
final class PaymentDto implements JsonSerializable
{
    /**
     * @param string       $orderNo      Unique merchant order reference
     * @param float        $amount       Payment amount (e.g. 100.00)
     * @param string       $successUrl   URL to redirect to on successful payment
     * @param string       $failedUrl    URL to redirect to on failed payment
     * @param string       $cancelUrl    URL to redirect to if user cancels
     * @param string       $backendUrl   Merchant endpoint to receive server-to-server notifications
     * @param array<string, mixed> $customFields  Any additional key/value data
     */
    public function __construct(
        private string $orderNo,
        private float $amount,
        private string $successUrl,
        private string $failedUrl,
        private string $cancelUrl,
        private string $backendUrl,
        private array $customFields = [],
    ) {}

    public function getOrderNo(): string
    {
        return $this->orderNo;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getSuccessUrl(): string
    {
        return $this->successUrl;
    }

    public function getFailedUrl(): string
    {
        return $this->failedUrl;
    }

    public function getCancelUrl(): string
    {
        return $this->cancelUrl;
    }

    public function getBackendUrl(): string
    {
        return $this->backendUrl;
    }

    /**
     * Returns any additional data the merchant wants to include.
     *
     * @return array<string, mixed>
     */
    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    /**
     * Convert the DTO into the array structure expected by the payment API.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'order_no'      => $this->orderNo,
            'amount'        => $this->amount,
            'success_url'   => $this->successUrl,
            'failed_url'    => $this->failedUrl,
            'cancel_url'    => $this->cancelUrl,
            'backend_url'   => $this->backendUrl,
            'custom_fields' => $this->customFields,
        ];
    }

    /**
     * Implement JsonSerializable so you can json_encode($dto) directly.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
