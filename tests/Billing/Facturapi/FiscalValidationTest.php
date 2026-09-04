<?php

declare(strict_types=1);

namespace SVR\Financial\Tests\Billing\Facturapi;

use PHPUnit\Framework\TestCase;
use SVR\Financial\Billing\Enums\BillingErrorCategory;
use SVR\Financial\Billing\Models\BillingError;
use SVR\Financial\Billing\Models\FiscalValidation;

final class FiscalValidationTest extends TestCase
{
    public function test_it_exposes_the_complete_billing_error(): void
    {
        $error = new BillingError(
            message: 'El RFC no coincide con los registros del SAT.',
            providerMessage: 'Tax ID mismatch',
            code: 'validation_error',
            httpCode: 422,
            category: BillingErrorCategory::Validation,
            path: 'customer.tax_id',
            logId: 'log_123',
        );

        $validation = new FiscalValidation(
            valid: false,
            message: $error->message,
            error: $error,
        );

        self::assertSame('validation_error', $validation->error?->code);
        self::assertSame(422, $validation->error?->httpCode);
        self::assertSame('customer.tax_id', $validation->error?->path);
        self::assertSame('log_123', $validation->error?->logId);
        self::assertSame(
            BillingErrorCategory::Validation,
            $validation->error?->category,
        );
    }
}
