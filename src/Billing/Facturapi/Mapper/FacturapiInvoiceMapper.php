<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Facturapi\Mapper;

use DateTimeImmutable;
use SVR\Financial\Billing\Enums\BillingProvider;
use SVR\Financial\Billing\Models\Invoice;

final class FacturapiInvoiceMapper
{
    public function fromResponse(object $invoice): Invoice
    {
        return new Invoice(
            provider: BillingProvider::Facturapi,
            providerId: (string) $invoice->id,
            uuid: isset($invoice->uuid)
                ? (string) $invoice->uuid
                : null,
            createdAt: isset($invoice->created_at)
                ? new DateTimeImmutable(
                    (string) $invoice->created_at
                )
                : null,
        );
    }
}