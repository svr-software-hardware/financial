<?php

declare(strict_types=1);

namespace SVR\Financial\Tests\Billing\Facturapi\Mapper;

use PHPUnit\Framework\TestCase;
use SVR\Financial\Billing\DTO\InvoiceItemData;
use SVR\Financial\Billing\DTO\PublicGeneralInvoiceData;
use SVR\Financial\Billing\DTO\TaxData;
use SVR\Financial\Billing\Enums\GlobalPeriodicity;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiInvoicePayloadMapper;

final class FacturapiInvoicePayloadMapperTest extends TestCase
{
    public function test_it_maps_a_public_general_invoice_with_sku(): void
    {
        $data = new PublicGeneralInvoiceData(
            items: [
                new InvoiceItemData(
                    description: 'Venta 123',
                    productKey: '01010101',
                    unitKey: 'H87',
                    price: 116.00,
                    taxes: [
                        new TaxData(
                            type: 'IVA',
                            rate: 0.16,
                        ),
                    ],
                    sku: ' sale_123 ',
                ),
            ],
            paymentForm: '04',
            issuerZipCode: '38000',
            periodicity: GlobalPeriodicity::Month,
            months: '09',
            year: 2026,
        );

        $payload = (new FacturapiInvoicePayloadMapper())
            ->mapPublicGeneral($data);

        self::assertSame('PUBLICO EN GENERAL', $payload['customer']['legal_name']);
        self::assertSame('XAXX010101000', $payload['customer']['tax_id']);
        self::assertSame('616', $payload['customer']['tax_system']);
        self::assertSame('38000', $payload['customer']['address']['zip']);
        self::assertSame('MEX', $payload['customer']['address']['country']);

        self::assertSame('S01', $payload['use']);
        self::assertSame('04', $payload['payment_form']);
        self::assertSame('PUE', $payload['payment_method']);

        self::assertSame('month', $payload['global']['periodicity']);
        self::assertSame('09', $payload['global']['months']);
        self::assertSame(2026, $payload['global']['year']);

        self::assertSame(
            'sale_123',
            $payload['items'][0]['product']['sku']
        );

        self::assertSame(
            'IVA',
            $payload['items'][0]['product']['taxes'][0]['type']
        );

        self::assertSame(
            0.16,
            $payload['items'][0]['product']['taxes'][0]['rate']
        );
    }

    public function test_it_omits_sku_when_it_is_not_provided(): void
    {
        $data = new PublicGeneralInvoiceData(
            items: [
                new InvoiceItemData(
                    description: 'Venta sin folio',
                    productKey: '01010101',
                    unitKey: 'H87',
                    price: 100.00,
                ),
            ],
            paymentForm: '01',
            issuerZipCode: '38000',
            periodicity: GlobalPeriodicity::Day,
            months: '09',
            year: 2026,
        );

        $payload = (new FacturapiInvoicePayloadMapper())
            ->mapPublicGeneral($data);

        self::assertArrayNotHasKey(
            'sku',
            $payload['items'][0]['product'],
        );
    }
}