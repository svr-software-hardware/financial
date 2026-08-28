<?php

declare(strict_types=1);

namespace SVR\Financial\Payments\Exceptions;

use RuntimeException;
use SVR\Financial\Payments\Enums\PaymentProvider;
use SVR\Financial\Payments\Models\ChargeError;
use Throwable;

final class PaymentProviderException extends RuntimeException
{
    public function __construct(
        public readonly PaymentProvider $provider,
        public readonly ChargeError $error,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $error->message,
            code: 0,
            previous: $previous,
        );
    }
}