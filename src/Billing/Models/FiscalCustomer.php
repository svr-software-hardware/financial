<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Models;

use DateTimeImmutable;
use SVR\Financial\Billing\Enums\BillingProvider;

final readonly class FiscalCustomer
{
    public function __construct(
        public BillingProvider $provider,
        public string $providerId,
        public string $taxId,
        public string $legalName,
        public string $taxSystem,
        public string $zipCode,
        public ?DateTimeImmutable $validatedAt = null,
    ) {
    }
}