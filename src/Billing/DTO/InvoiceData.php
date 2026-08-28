<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\DTO;

final readonly class InvoiceData
{
    /**
     * @param array<InvoiceItemData> $items
     */
    public function __construct(
        public FiscalCustomerData $customer,
        public array $items,
        public string $cfdiUsage,
        public string $paymentForm,
        public string $paymentMethod = 'PUE',
    ) {
    }
}