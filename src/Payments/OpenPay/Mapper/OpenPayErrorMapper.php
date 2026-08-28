<?php

declare(strict_types=1);

namespace SVR\Financial\Payments\OpenPay\Mapper;

use Openpay\Data\OpenpayApiAuthError;
use Openpay\Data\OpenpayApiConnectionError;
use Openpay\Data\OpenpayApiError;
use Openpay\Data\OpenpayApiRequestError;
use Openpay\Data\OpenpayApiTransactionError;
use SVR\Financial\Payments\Models\ChargeError;
use Throwable;

final class OpenPayErrorMapper
{
    public function map(Throwable $error): ChargeError
    {
        if (!($error instanceof OpenpayApiError)) {
            return new ChargeError(
                code: null,
                message: 'No fue posible procesar el pago.',
                providerMessage: $error->getMessage(),
            );
        }

        return new ChargeError(
            code: $this->stringOrNull($error->getErrorCode()),
            message: $this->publicMessage($error),
            providerMessage: $this->stringOrNull(
                $error->getDescription()
            ) ?? $error->getMessage(),
            httpCode: $this->positiveIntegerOrNull(
                $error->getHttpCode()
            ),
            providerRequestId: $this->stringOrNull(
                $error->getRequestId()
            ),
            category: $this->stringOrNull(
                $error->getCategory()
            ),
        );
    }

    private function publicMessage(Throwable $error): string
    {
        return match (true) {
            $error instanceof OpenpayApiTransactionError
                => 'El pago fue rechazado por el proveedor.',

            $error instanceof OpenpayApiRequestError
                => 'Los datos enviados para procesar el pago no son válidos.',

            $error instanceof OpenpayApiAuthError
                => 'La configuración de OpenPay no es válida.',

            $error instanceof OpenpayApiConnectionError
                => 'No fue posible comunicarse con OpenPay.',

            default
                => 'No fue posible procesar el pago.',
        };
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' || $value === '0'
            ? null
            : $value;
    }

    private function positiveIntegerOrNull(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }
}