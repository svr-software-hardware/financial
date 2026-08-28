<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Facturapi\Mapper;

use DateTimeImmutable;
use SVR\Financial\Billing\DTO\FiscalCustomerData;
use SVR\Financial\Billing\Enums\BillingProvider;
use SVR\Financial\Billing\Models\FiscalCustomer;

final class FacturapiFiscalCustomerMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toPayload(
        FiscalCustomerData $data,
    ): array {
        return [
            'tax_id' => $data->taxId,
            'legal_name' => $data->legalName,
            'tax_system' => $data->taxSystem,
            'address' => [
                'zip' => $data->zipCode,
                'country' => $data->country,
            ],
        ];
    }

    public function fromResponse(
        object $customer,
    ): FiscalCustomer {
        return new FiscalCustomer(
            provider: BillingProvider::Facturapi,
            providerId: (string) $customer->id,
            taxId: (string) $customer->tax_id,
            legalName: (string) $customer->legal_name,
            taxSystem: (string) $customer->tax_system,
            zipCode: (string) ($customer->address->zip ?? ''),
            validatedAt: isset($customer->sat_validated_at)
                && $customer->sat_validated_at !== null
                    ? new DateTimeImmutable(
                        (string) $customer->sat_validated_at
                    )
                    : null,
        );
    }
}