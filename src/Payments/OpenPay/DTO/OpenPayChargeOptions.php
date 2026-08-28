<?php

declare(strict_types=1);

namespace SVR\Financial\Payments\OpenPay\DTO;

use SVR\Financial\Payments\Contracts\ChargeProviderOptions;

final readonly class OpenPayChargeOptions implements ChargeProviderOptions
{
    public function __construct(
        public string $deviceSessionId,
        public bool $useCardPoints = false,
        public ?string $customerIp = null,
    ) {
    }
}