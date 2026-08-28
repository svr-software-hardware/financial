<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\DTO;

final readonly class InvoiceItemData
{
    /**
     * @param array<TaxData> $taxes
     */
    public function __construct(
        public string $description,
        public string $productKey,
        public string $unitKey,
        public float $price,
        public float $quantity = 1,
        public float $discount = 0,
        public bool $taxIncluded = true,
        public array $taxes = [],
    ) {
    }
}