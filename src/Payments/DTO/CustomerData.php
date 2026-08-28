<?php

declare(strict_types=1);

namespace SVR\Financial\Payments\DTO;

final readonly class CustomerData
{
    public function __construct(
        public string $name,
        public string $lastName,
        public string $email,
        public string $phone,
    ) {
    }
}