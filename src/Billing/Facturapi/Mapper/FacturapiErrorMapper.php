<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Facturapi\Mapper;

use Facturapi\Exceptions\FacturapiException;
use SVR\Financial\Billing\Enums\BillingErrorCategory;
use SVR\Financial\Billing\Models\BillingError;
use Throwable;

final class FacturapiErrorMapper
{
    public function map(Throwable $error): BillingError
    {
        $providerMessage = $this->extractProviderMessage($error);

        $httpCode = $this->extractHttpCode(
            $error
        );

        $category = $this->category(
            $providerMessage,
            $httpCode,
        );

        return new BillingError(
            message: $this->publicMessage(
                $category,
                $providerMessage,
            ),
            providerMessage: $providerMessage,
            code: $this->extractCode($error),
            httpCode: $httpCode,
            category: $category,
            path: $this->extractString($error, 'getErrorPath'),
            logId: $this->extractString($error, 'getLogId'),
        );
    }

    private function category(
        string $providerMessage,
        ?int $httpCode,
    ): BillingErrorCategory {
        $message = mb_strtolower(
            $providerMessage
        );

        /*
         * Errores de conexión / SSL / DNS / timeout.
         */
        if (
            str_contains($message, 'curl error')
            || str_contains($message, 'ssl certificate')
            || str_contains($message, 'timed out')
            || str_contains($message, 'timeout')
            || str_contains($message, 'could not resolve host')
            || str_contains($message, 'connection refused')
            || str_contains($message, 'failed to connect')
        ) {
            return BillingErrorCategory::Connection;
        }

        /*
         * Autenticación/autorización.
         */
        if (
            $httpCode === 401
            || $httpCode === 403
            || str_contains($message, 'unauthorized')
            || str_contains($message, 'invalid api key')
            || str_contains($message, 'authentication')
        ) {
            return BillingErrorCategory::Authentication;
        }

        /*
         * Datos fiscales / payload inválido.
         */
        if (
            $httpCode === 400
            || $httpCode === 422
            || str_contains($message, 'rfc')
            || str_contains($message, 'tax_id')
            || str_contains($message, 'tax id')
            || str_contains($message, 'tax_system')
            || str_contains($message, 'tax system')
            || str_contains($message, 'legal_name')
            || str_contains($message, 'legal name')
            || str_contains($message, 'razón social')
            || str_contains($message, 'razon social')
            || str_contains($message, 'código postal')
            || str_contains($message, 'codigo postal')
            || str_contains($message, 'zip')
        ) {
            return BillingErrorCategory::Validation;
        }

        /*
         * Error del proveedor.
         */
        if (
            $httpCode !== null
            && $httpCode >= 500
        ) {
            return BillingErrorCategory::Provider;
        }

        return BillingErrorCategory::Unknown;
    }

    private function publicMessage(
        BillingErrorCategory $category,
        string $providerMessage,
    ): string {
        return match ($category) {
            BillingErrorCategory::Configuration =>
                'La configuración del proveedor de facturación no es válida.',

            BillingErrorCategory::Connection =>
                'No fue posible comunicarse de forma segura con Facturapi.',

            BillingErrorCategory::Authentication =>
                'Facturapi rechazó las credenciales proporcionadas.',

            BillingErrorCategory::Validation =>
                $providerMessage !== ''
                    ? $providerMessage
                    : 'La información proporcionada no pudo ser validada.',

            BillingErrorCategory::Provider =>
                'Facturapi no pudo procesar la operación.',

            BillingErrorCategory::Unknown =>
                'Ocurrió un error al procesar la operación de facturación.',
        };
    }

    private function extractCode(
        Throwable $error,
    ): ?string {
        $providerCode = $this->extractString(
            $error,
            'getErrorCode',
        );

        if ($providerCode !== null) {
            return $providerCode;
        }

        $code = $error->getCode();

        return $code !== 0
            ? (string) $code
            : null;
    }

    private function extractHttpCode(
        Throwable $error,
    ): ?int {
        if ($error instanceof FacturapiException) {
            return $error->getStatusCode();
        }

        if (method_exists($error, 'getStatusCode')) {
            $statusCode = $error->getStatusCode();

            if (is_int($statusCode)) {
                return $statusCode;
            }
        }

        if (!method_exists($error, 'getResponse')) {
            return null;
        }

        $response = $error->getResponse();

        if ($response === null) {
            return null;
        }

        if (!method_exists($response, 'getStatusCode')) {
            return null;
        }

        return (int) $response->getStatusCode();
    }

    private function extractProviderMessage(
        Throwable $error,
    ): string {
        if ($error instanceof FacturapiException) {
            $message = $this->firstMessage(
                $error->getErrors()
            );

            if ($message !== null) {
                return $message;
            }

            $message = $this->firstMessage(
                $error->getErrorData()
            );

            if ($message !== null) {
                return $message;
            }
        }

        return trim($error->getMessage());
    }

    private function firstMessage(mixed $value): ?string
    {
        if (is_string($value)) {
            $value = trim($value);

            return $value !== '' ? $value : null;
        }

        if (!is_array($value)) {
            return null;
        }

        foreach (['message', 'details', 'errors'] as $key) {
            if (!array_key_exists($key, $value)) {
                continue;
            }

            $message = $this->firstMessage($value[$key]);

            if ($message !== null) {
                return $message;
            }
        }

        foreach ($value as $item) {
            $message = $this->firstMessage($item);

            if ($message !== null) {
                return $message;
            }
        }

        return null;
    }

    private function extractString(
        Throwable $error,
        string $method,
    ): ?string {
        if (!method_exists($error, $method)) {
            return null;
        }

        $value = $error->{$method}();

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
