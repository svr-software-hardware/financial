<?php

declare(strict_types=1);

namespace SVR\Financial\Tests\Billing\Facturapi\Mapper;

use Facturapi\Exceptions\FacturapiException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SVR\Financial\Billing\Enums\BillingErrorCategory;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiErrorMapper;

final class FacturapiErrorMapperTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function fiscalValidationErrors(): iterable
    {
        yield 'RFC' => [
            'customer.tax_id',
            'El RFC no coincide con los registros del SAT.',
        ];

        yield 'razón social' => [
            'customer.legal_name',
            'La razón social no coincide con el RFC.',
        ];

        yield 'régimen fiscal' => [
            'customer.tax_system',
            'El régimen fiscal no es válido para este contribuyente.',
        ];

        yield 'código postal' => [
            'customer.address.zip',
            'El código postal no coincide con los registros fiscales.',
        ];
    }

    #[DataProvider('fiscalValidationErrors')]
    public function test_it_exposes_facturapi_validation_details(
        string $path,
        string $message,
    ): void {
        $error = new FacturapiException(
            message: 'Validation failed',
            errorData: [
                'code' => 'validation_error',
                'path' => $path,
                'errors' => [
                    ['message' => $message],
                ],
            ],
            statusCode: 422,
            headers: [
                'x-facturapi-log-id' => 'log_123',
            ],
        );

        $mapped = (new FacturapiErrorMapper())->map($error);

        self::assertSame($message, $mapped->message);
        self::assertSame($message, $mapped->providerMessage);
        self::assertSame('validation_error', $mapped->code);
        self::assertSame(422, $mapped->httpCode);
        self::assertSame(BillingErrorCategory::Validation, $mapped->category);
        self::assertSame($path, $mapped->path);
        self::assertSame('log_123', $mapped->logId);
    }

    public function test_it_extracts_a_detail_from_error_data(): void
    {
        $error = new FacturapiException(
            message: 'Validation failed',
            errorData: [
                'details' => [
                    ['message' => 'El RFC tiene un formato inválido.'],
                ],
            ],
            statusCode: 400,
        );

        $mapped = (new FacturapiErrorMapper())->map($error);

        self::assertSame('El RFC tiene un formato inválido.', $mapped->message);
        self::assertSame(BillingErrorCategory::Validation, $mapped->category);
    }

    public function test_it_hides_authentication_details(): void
    {
        $error = new FacturapiException(
            message: 'Secret key sk_live_sensitive is invalid',
            errorData: ['message' => 'Secret key sk_live_sensitive is invalid'],
            statusCode: 401,
        );

        $mapped = (new FacturapiErrorMapper())->map($error);

        self::assertSame(
            'Facturapi rechazó las credenciales proporcionadas.',
            $mapped->message,
        );
        self::assertSame(BillingErrorCategory::Authentication, $mapped->category);
        self::assertStringNotContainsString('sk_live_sensitive', $mapped->message);
    }

    public function test_it_hides_server_error_details(): void
    {
        $error = new FacturapiException(
            message: 'Internal database host:5432 unavailable',
            errorData: ['message' => 'Internal database host:5432 unavailable'],
            statusCode: 500,
        );

        $mapped = (new FacturapiErrorMapper())->map($error);

        self::assertSame(
            'Facturapi no pudo procesar la operación.',
            $mapped->message,
        );
        self::assertSame(BillingErrorCategory::Provider, $mapped->category);
        self::assertStringNotContainsString('host:5432', $mapped->message);
    }
}
