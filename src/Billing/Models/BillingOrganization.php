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
        public bool $certificateLoaded = false,
        public ?DateTimeImmutable $certificateUpdatedAt = null,
        public ?DateTimeImmutable $certificateExpiresAt = null,
        public ?string $certificateSerialNumber = null,
    ) {
    }

    public function certificateExpired(
        ?DateTimeImmutable $at = null,
    ): bool {
        if (!$this->certificateLoaded) {
            return false;
        }

        if ($this->certificateExpiresAt === null) {
            return false;
        }

        return $this->certificateExpiresAt <= (
            $at ?? new DateTimeImmutable()
        );
    }
}