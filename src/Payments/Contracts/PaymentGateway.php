<?php

declare(strict_types=1);

namespace SVR\Financial\Payments\Contracts;

use SVR\Financial\Payments\DTO\CardChargeData;
use SVR\Financial\Payments\Enums\PaymentProvider;
use SVR\Financial\Payments\Models\Charge;

interface PaymentGateway
{
    public function provider(): PaymentProvider;

    public function charge(CardChargeData $data): Charge;

    public function getCharge(string $providerId): Charge;
}