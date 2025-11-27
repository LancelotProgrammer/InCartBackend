<?php

namespace App\Services;

use InvalidArgumentException;

class PaymentGatewayResult
{
    public function __construct(
        public bool $success,
        public ?string $message = null,
        public ?array $data = null
    ) {
        if (! $this->success && empty($this->message)) {
            throw new InvalidArgumentException(
                'A failure PaymentGatewayResult must include a message.'
            );
        }
    }

    public function isSuccessful(): bool
    {
        return $this->success;
    }

    public function isFailed(): bool
    {
        return ! $this->success;
    }

    public function hasData(): bool
    {
        return $this->data !== null;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
