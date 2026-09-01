<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Models;

use SVR\Financial\Billing\Enums\BillingErrorCategory;

final readonly class BillingError
{
    public function __construct(
        public string $message,
        public ?string $providerMessage = null,
        public ?string $code = null,
        public ?int $httpCode = null,
        public BillingErrorCategory $category = BillingErrorCategory::Unknown,
        public ?string $path = null,
        public ?string $logId = null,
    ) {
    }
}
