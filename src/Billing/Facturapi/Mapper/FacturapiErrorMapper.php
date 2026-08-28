<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Facturapi\Mapper;

use SVR\Financial\Billing\Enums\BillingErrorCategory;
use SVR\Financial\Billing\Models\BillingError;
use Throwable;

final class FacturapiErrorMapper
{
    public function map(Throwable $error): BillingError
    {
        $providerMessage = trim(
            $error->getMessage()
        );

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
                $this->validationMessage(
                    $providerMessage
                ),

            BillingErrorCategory::Provider =>
                'Facturapi no pudo procesar la operación.',

            BillingErrorCategory::Unknown =>
                'Ocurrió un error al procesar la operación de facturación.',
        };
    }

    private function validationMessage(
        string $providerMessage,
    ): string {
        $message = mb_strtolower(
            $providerMessage
        );

        if (
            str_contains($message, 'rfc')
            || str_contains($message, 'tax_id')
            || str_contains($message, 'tax id')
        ) {
            return 'El RFC no pudo ser validado.';
        }

        if (
            str_contains($message, 'tax_system')
            || str_contains($message, 'tax system')
            || str_contains($message, 'régimen')
            || str_contains($message, 'regimen')
        ) {
            return 'El régimen fiscal no es válido para este contribuyente.';
        }

        if (
            str_contains($message, 'legal_name')
            || str_contains($message, 'legal name')
            || str_contains($message, 'razón social')
            || str_contains($message, 'razon social')
            || str_contains($message, 'nombre')
        ) {
            return 'El nombre o razón social no coincide con los registros fiscales.';
        }

        if (
            str_contains($message, 'zip')
            || str_contains($message, 'código postal')
            || str_contains($message, 'codigo postal')
        ) {
            return 'El código postal fiscal no coincide con los registros fiscales.';
        }

        return 'La información proporcionada no pudo ser validada.';
    }

    private function extractCode(
        Throwable $error,
    ): ?string {
        $code = $error->getCode();

        return $code !== 0
            ? (string) $code
            : null;
    }

    private function extractHttpCode(
        Throwable $error,
    ): ?int {
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
}