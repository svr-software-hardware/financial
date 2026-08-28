<?php

declare(strict_types=1);

namespace SVR\Financial\Payments\Models;

final readonly class ChargeError
{
    public function __construct(
        public ?string $code,
        public string $message,
        public ?string $providerMessage = null,
        public ?int $httpCode = null,
        public ?string $providerRequestId = null,
        public ?string $category = null,
    ) {
    }
}