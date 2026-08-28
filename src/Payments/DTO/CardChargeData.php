<?php

declare(strict_types=1);

namespace SVR\Financial\Payments\DTO;

use SVR\Financial\Payments\Contracts\ChargeProviderOptions;

final readonly class CardChargeData
{
    public function __construct(
        public float $amount,
        public string $description,
        public string $paymentMethodId,
        public CustomerData $customer,
        public bool $use3DSecure = false,
        public ?string $redirectUrl = null,
        public ?ChargeProviderOptions $providerOptions = null,
    ) {
    }
}