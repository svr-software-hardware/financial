<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\DTO;

final readonly class OrganizationData
{
    public function __construct(
        public string $name,
    ) {
        if (trim($this->name) === '') {
            throw new \InvalidArgumentException(
                'El nombre de la organización no puede estar vacío.'
            );
        }
    }
}