<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\DTO;

use InvalidArgumentException;

final readonly class OrganizationLegalData
{
    public function __construct(
        public string $name,
        public string $legalName,
        public string $taxSystem,
        public OrganizationAddressData $address,
        public ?string $website = null,
        public ?string $supportEmail = null,
        public ?string $phone = null,
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'El nombre comercial de la organización no puede estar vacío.'
            );
        }

        if (trim($this->legalName) === '') {
            throw new InvalidArgumentException(
                'La razón social de la organización no puede estar vacía.'
            );
        }

        if (strlen(trim($this->taxSystem)) !== 3) {
            throw new InvalidArgumentException(
                'El régimen fiscal debe ser un código SAT de 3 caracteres.'
            );
        }
    }
}