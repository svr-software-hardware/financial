<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Exceptions;

use RuntimeException;
use SVR\Financial\Billing\Models\BillingError;
use Throwable;

final class FiscalValidationException extends RuntimeException
{
    public function __construct(
        public readonly BillingError $error,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $error->message,
            previous: $previous,
        );
    }
}