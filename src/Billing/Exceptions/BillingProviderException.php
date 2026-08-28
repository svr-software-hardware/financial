<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Exceptions;

use RuntimeException;
use SVR\Financial\Billing\Enums\BillingProvider;
use SVR\Financial\Billing\Models\BillingError;
use Throwable;

final class BillingProviderException extends RuntimeException
{
    public function __construct(
        public readonly BillingProvider $provider,
        public readonly BillingError $error,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $error->message,
            previous: $previous,
        );
    }
}