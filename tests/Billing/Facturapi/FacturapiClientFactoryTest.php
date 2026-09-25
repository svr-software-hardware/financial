<?php

declare(strict_types=1);

namespace SVR\Financial\Tests\Billing\Facturapi;

use Facturapi\Facturapi;
use PHPUnit\Framework\TestCase;
use SVR\Financial\Billing\DTO\BillingContext;
use SVR\Financial\Billing\Facturapi\Exceptions\FacturapiConfigurationException;
use SVR\Financial\Billing\Facturapi\FacturapiClientFactory;

final class FacturapiClientFactoryTest extends TestCase
{
    public function test_production_organization_requires_an_explicit_live_api_key(): void
    {
        $factory = new FacturapiClientFactory(
            apiKey: 'sk_live_main',
            userKey: 'sk_user_test',
            production: true,
        );

        $this->expectException(
            FacturapiConfigurationException::class
        );

        $this->expectExceptionMessage(
            'La Live API Key de la organización es obligatoria en producción.'
        );

        $factory->create(
            BillingContext::organization(
                'organization_123'
            )
        );
    }

    public function test_production_organization_accepts_an_explicit_live_api_key(): void
    {
        $factory = new FacturapiClientFactory(
            apiKey: null,
            userKey: null,
            production: true,
        );

        $client = $factory->create(
            BillingContext::organization(
                'organization_123',
                'sk_live_organization',
            )
        );

        self::assertInstanceOf(
            Facturapi::class,
            $client
        );
    }
}