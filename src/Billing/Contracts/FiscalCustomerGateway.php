<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Contracts;

use SVR\Financial\Billing\DTO\BillingContext;
use SVR\Financial\Billing\DTO\FiscalCustomerData;
use SVR\Financial\Billing\Models\FiscalCustomer;
use SVR\Financial\Billing\Models\FiscalValidation;

interface FiscalCustomerGateway
{
    public function createCustomer(
        FiscalCustomerData $data,
        BillingContext $context,
    ): FiscalCustomer;

    public function validateCustomerData(
        FiscalCustomerData $data,
        BillingContext $context,
    ): FiscalValidation;

    public function deleteCustomer(
        string $providerCustomerId,
        BillingContext $context,
    ): void;
}