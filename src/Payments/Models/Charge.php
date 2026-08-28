<?php

declare(strict_types=1);

namespace SVR\Financial\Payments\Models;

use DateTimeImmutable;
use SVR\Financial\Payments\Enums\PaymentProvider;
use SVR\Financial\Payments\Enums\ChargeStatus;

final readonly class Charge
{
    public function __construct(
        public PaymentProvider $provider,
        public ?string $providerId,
        public ChargeStatus $status,
        public float $amount,
        public ?string $authorization = null,
        public ?DateTimeImmutable $operationDate = null,
        public ?Card $card = null,
        public ?string $redirectUrl = null,
        public ?ChargeError $error = null,
    ) {
    }

    public function successful(): bool
    {
        return $this->status === ChargeStatus::Completed;
    }

    public function failed(): bool
    {
        return $this->status === ChargeStatus::Failed;
    }

    public function pending(): bool
    {
        return $this->status === ChargeStatus::Pending;
    }

    public function requiresAction(): bool
    {
        return $this->pending() && $this->redirectUrl !== null;
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }
}