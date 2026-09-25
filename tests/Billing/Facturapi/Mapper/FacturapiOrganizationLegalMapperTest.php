<?php

declare(strict_types=1);

namespace SVR\Financial\Tests\Billing\Facturapi\Mapper;

use PHPUnit\Framework\TestCase;
use SVR\Financial\Billing\DTO\OrganizationAddressData;
use SVR\Financial\Billing\DTO\OrganizationLegalData;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiOrganizationLegalMapper;

final class FacturapiOrganizationLegalMapperTest extends TestCase
{
    public function test_it_maps_organization_legal_data_and_omits_blank_optional_values(): void
    {
        $data = new OrganizationLegalData(
            name: ' Distribuidor Demo ',
            legalName: ' DISTRIBUIDOR DEMO SA DE CV ',
            taxSystem: ' 601 ',
            address: new OrganizationAddressData(
                zip: ' 38000 ',
                street: ' Hidalgo ',
                exterior: ' 10 ',
                interior: '   ',
                city: ' Celaya ',
                municipality: ' Celaya ',
                state: ' Guanajuato ',
            ),
            website: ' https://example.com ',
            supportEmail: '   ',
            phone: '4610000000',
        );

        $payload = (new FacturapiOrganizationLegalMapper())
            ->toPayload($data);

        self::assertSame(
            'Distribuidor Demo',
            $payload['name']
        );

        self::assertSame(
            'DISTRIBUIDOR DEMO SA DE CV',
            $payload['legal_name']
        );

        self::assertSame(
            '601',
            $payload['tax_system']
        );

        self::assertSame(
            '38000',
            $payload['address']['zip']
        );

        self::assertSame(
            'Hidalgo',
            $payload['address']['street']
        );

        self::assertSame(
            '10',
            $payload['address']['exterior']
        );

        self::assertSame(
            'Celaya',
            $payload['address']['city']
        );

        self::assertSame(
            'Celaya',
            $payload['address']['municipality']
        );

        self::assertSame(
            'Guanajuato',
            $payload['address']['state']
        );

        self::assertArrayNotHasKey(
            'interior',
            $payload['address']
        );

        self::assertSame(
            'https://example.com',
            $payload['website']
        );

        self::assertArrayNotHasKey(
            'support_email',
            $payload
        );

        self::assertSame(
            '4610000000',
            $payload['phone']
        );
    }
}