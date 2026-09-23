<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Models;

use DateTimeImmutable;
use SVR\Financial\Billing\Enums\BillingProvider;

final readonly class BillingOrganization
{
    /**
     * @param array<int, string> $pendingSteps
     */
    public function __construct(
        public BillingProvider $provider,
        public string $providerId,
        public ?string $name = null,
        public bool $productionReady = false,
        public array $pendingSteps = [],
        public ?DateTimeImmutable $createdAt = null,
    ) {
    }
}