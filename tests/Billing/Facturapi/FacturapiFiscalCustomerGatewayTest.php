<?php

declare(strict_types=1);

namespace SVR\Financial\Tests\Billing\Facturapi;

use PHPUnit\Framework\TestCase;
use SVR\Financial\Billing\DTO\BillingContext;
use SVR\Financial\Billing\DTO\FiscalCustomerData;
use SVR\Financial\Billing\Enums\BillingErrorCategory;
use SVR\Financial\Billing\Facturapi\FacturapiClientFactory;
use SVR\Financial\Billing\Facturapi\FacturapiFiscalCustomerGateway;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiErrorMapper;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiFiscalCustomerMapper;

final class FacturapiFiscalCustomerGatewayTest extends TestCase
{
    public function test_fiscal_validation_preserves_the_complete_billing_error(): void
    {
        $gateway = new FacturapiFiscalCustomerGateway(
            clientFactory: new FacturapiClientFactory(
                apiKey: null,
                userKey: null,
            ),
            customerMapper: new FacturapiFiscalCustomerMapper(),
            errorMapper: new FacturapiErrorMapper(),
        );

        $validation = $gateway->validateCustomerData(
            new FiscalCustomerData(
                taxId: 'XAXX010101000',
                legalName: 'PÚBLICO EN GENERAL',
                taxSystem: '616',
                zipCode: '06000',
            ),
            BillingContext::default(),
        );

        self::assertFalse($validation->valid);
        self::assertSame(
            'La configuración del proveedor de facturación no es válida.',
            $validation->message,
        );
        self::assertNotNull($validation->error);
        self::assertSame($validation->message, $validation->error->message);
        self::assertSame(
            'La API Key principal de Facturapi no está configurada.',
            $validation->error->providerMessage,
        );
        self::assertNull($validation->error->code);
        self::assertNull($validation->error->httpCode);
        self::assertNull($validation->error->path);
        self::assertNull($validation->error->logId);
        self::assertSame(
            BillingErrorCategory::Configuration,
            $validation->error->category,
        );
    }
}
