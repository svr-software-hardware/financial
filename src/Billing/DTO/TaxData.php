<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\DTO;

final readonly class TaxData
{
    public function __construct(
        public string $type,
        public float $rate,
    ) {
    }
}