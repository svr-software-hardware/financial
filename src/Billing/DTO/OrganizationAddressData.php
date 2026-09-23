<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\DTO;

use InvalidArgumentException;

final readonly class OrganizationAddressData
{
    public function __construct(
        public string $zip,
        public ?string $street = null,
        public ?string $exterior = null,
        public ?string $interior = null,
        public ?string $neighborhood = null,
        public ?string $city = null,
        public ?string $municipality = null,
        public ?string $state = null,
    ) {
        if (trim($this->zip) === '') {
            throw new InvalidArgumentException(
                'El código postal de la organización no puede estar vacío.'
            );
        }
    }
}