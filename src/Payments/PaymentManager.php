<?php

declare(strict_types=1);

namespace SVR\Financial\Payments;

use SVR\Financial\Payments\Contracts\PaymentGateway;
use SVR\Financial\Payments\DTO\CardChargeData;
use SVR\Financial\Payments\Enums\PaymentProvider;
use SVR\Financial\Payments\Models\Charge;

final readonly class PaymentManager
{
    public function __construct(
        private PaymentGateway $gateway,
    ) {
    }

    public function provider(): PaymentProvider
    {
        return $this->gateway->provider();
    }

    public function charge(CardChargeData $data): Charge
    {
        return $this->gateway->charge($data);
    }

    public function getCharge(string $providerId): Charge
    {
        return $this->gateway->getCharge($providerId);
    }
}