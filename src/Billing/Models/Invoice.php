<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Models;

use DateTimeImmutable;
use SVR\Financial\Billing\Enums\BillingProvider;

final readonly class Invoice
{
    public function __construct(
        public BillingProvider $provider,
        public string $providerId,
        public ?string $uuid = null,
        public ?DateTimeImmutable $createdAt = null,
    ) {
    }
}