<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Facturapi\Mapper;

use SVR\Financial\Billing\DTO\OrganizationAddressData;
use SVR\Financial\Billing\DTO\OrganizationLegalData;

final class FacturapiOrganizationLegalMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toPayload(
        OrganizationLegalData $data,
    ): array {
        $payload = [
            'name' => trim($data->name),
            'legal_name' => trim($data->legalName),
            'tax_system' => trim($data->taxSystem),
            'address' => $this->mapAddress(
                $data->address
            ),
        ];

        $this->addOptional(
            $payload,
            'website',
            $data->website
        );

        $this->addOptional(
            $payload,
            'support_email',
            $data->supportEmail
        );

        $this->addOptional(
            $payload,
            'phone',
            $data->phone
        );

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapAddress(
        OrganizationAddressData $address,
    ): array {
        $payload = [
            'zip' => trim($address->zip),
        ];

        $this->addOptional(
            $payload,
            'street',
            $address->street
        );

        $this->addOptional(
            $payload,
            'exterior',
            $address->exterior
        );

        $this->addOptional(
            $payload,
            'interior',
            $address->interior
        );

        $this->addOptional(
            $payload,
            'neighborhood',
            $address->neighborhood
        );

        $this->addOptional(
            $payload,
            'city',
            $address->city
        );

        $this->addOptional(
            $payload,
            'municipality',
            $address->municipality
        );

        $this->addOptional(
            $payload,
            'state',
            $address->state
        );

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function addOptional(
        array &$payload,
        string $key,
        ?string $value,
    ): void {
        if ($value === null) {
            return;
        }

        $value = trim($value);

        if ($value === '') {
            return;
        }

        $payload[$key] = $value;
    }
}