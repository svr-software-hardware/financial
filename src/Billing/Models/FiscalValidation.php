<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Models;

final readonly class FiscalValidation
{
    public function __construct(
        public bool $valid,
        public ?string $message = null,
        public ?string $providerCustomerId = null,
    ) {
    }
}