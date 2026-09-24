<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Contracts;

use SVR\Financial\Billing\DTO\BillingContext;
use SVR\Financial\Billing\DTO\InvoiceData;
use SVR\Financial\Billing\DTO\PublicGeneralInvoiceData;
use SVR\Financial\Billing\Enums\BillingProvider;
use SVR\Financial\Billing\Models\Invoice;

interface InvoiceGateway
{
    public function provider(): BillingProvider;

    public function stamp(
        InvoiceData $data,
        BillingContext $context,
    ): Invoice;

    public function stampPublicGeneral(
        PublicGeneralInvoiceData $data,
        BillingContext $context,
    ): Invoice;

    public function downloadPdf(
        string $providerId,
        BillingContext $context,
    ): string;

    public function downloadXml(
        string $providerId,
        BillingContext $context,
    ): string;
}