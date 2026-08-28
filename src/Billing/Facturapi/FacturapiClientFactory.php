<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Facturapi;

use Facturapi\Facturapi;
use SVR\Financial\Billing\DTO\BillingContext;
use SVR\Financial\Billing\Facturapi\Exceptions\FacturapiConfigurationException;

final readonly class FacturapiClientFactory
{
    public function __construct(
        private ?string $apiKey,
        private ?string $userKey,
        private bool $production = false,
    ) {
    }

    public function create(
        BillingContext $context,
    ): Facturapi {
        if (!$context->usesOrganization()) {
            return new Facturapi(
                $this->requireApiKey()
            );
        }

        return $this->createForOrganization(
            $context
        );
    }

    public function createUserClient(): Facturapi
    {
        return new Facturapi(
            $this->requireUserKey()
        );
    }

    private function createForOrganization(
        BillingContext $context,
    ): Facturapi {
        /*
         * Si la aplicación ya nos entrega una API Key
         * específica de la organización, la utilizamos.
         *
         * En producción esta será la forma normal de trabajar.
         */
        if (
            $context->organizationApiKey !== null
            && $context->organizationApiKey !== ''
        ) {
            return new Facturapi(
                $context->organizationApiKey
            );
        }

        /*
         * En ambiente Test podemos recuperar automáticamente
         * la Test API Key mediante la User Secret Key.
         */
        if (!$this->production) {
            $userClient = $this->createUserClient();

            $apiKey = $userClient
                ->Organizations
                ->getTestApiKey(
                    $context->organizationId
                );

            if (
                !is_string($apiKey)
                || trim($apiKey) === ''
            ) {
                throw new FacturapiConfigurationException(
                    'Facturapi no devolvió una Test API Key válida para la organización.'
                );
            }

            return new Facturapi($apiKey);
        }

        /*
         * Las Live Secret Keys no pueden recuperarse completas
         * posteriormente. Deben almacenarse al generarlas.
         */
        throw new FacturapiConfigurationException(
            'La Live API Key de la organización es obligatoria en producción.'
        );
    }

    private function requireApiKey(): string
    {
        $apiKey = trim(
            (string) $this->apiKey
        );

        if ($apiKey === '') {
            throw new FacturapiConfigurationException(
                'La API Key principal de Facturapi no está configurada.'
            );
        }

        return $apiKey;
    }

    private function requireUserKey(): string
    {
        $userKey = trim(
            (string) $this->userKey
        );

        if ($userKey === '') {
            throw new FacturapiConfigurationException(
                'La User Secret Key de Facturapi no está configurada.'
            );
        }

        return $userKey;
    }
}