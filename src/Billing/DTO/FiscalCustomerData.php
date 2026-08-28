<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\DTO;

final readonly class FiscalCustomerData
{
    public function __construct(
        public string $taxId,
        public string $legalName,
        public string $taxSystem,
        public string $zipCode,
        public string $country = 'MEX',
    ) {
    }
}