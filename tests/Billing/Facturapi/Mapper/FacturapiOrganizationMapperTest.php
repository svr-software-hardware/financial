<?php

declare(strict_types=1);

namespace SVR\Financial\Tests\Billing\Facturapi\Mapper;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiOrganizationMapper;

final class FacturapiOrganizationMapperTest extends TestCase
{
    public function test_it_maps_organization_certificate_status(): void
    {
        $response = (object) [
            'id' => 'org_123',
            'created_at' => '2026-09-01T10:00:00.000Z',
            'is_production_ready' => true,
            'pending_steps' => [],
            'legal' => (object) [
                'name' => 'Distribuidor Demo',
            ],
            'certificate' => (object) [
                'has_certificate' => true,
                'updated_at' => '2026-09-15T12:00:00.000Z',
                'expires_at' => '2030-09-15T12:00:00.000Z',
                'serial_number' => '30001000000300000101',
            ],
        ];

        $organization = (
            new FacturapiOrganizationMapper()
        )->fromResponse($response);

        self::assertSame(
            'org_123',
            $organization->providerId
        );

        self::assertSame(
            'Distribuidor Demo',
            $organization->name
        );

        self::assertTrue(
            $organization->productionReady
        );

        self::assertTrue(
            $organization->certificateLoaded
        );

        self::assertSame(
            '30001000000300000101',
            $organization->certificateSerialNumber
        );

        self::assertSame(
            '2030-09-15T12:00:00+00:00',
            $organization
                ->certificateExpiresAt
                ?->format(DATE_ATOM)
        );

        self::assertFalse(
            $organization->certificateExpired(
                new DateTimeImmutable(
                    '2029-01-01T00:00:00+00:00'
                )
            )
        );

        self::assertTrue(
            $organization->certificateExpired(
                new DateTimeImmutable(
                    '2031-01-01T00:00:00+00:00'
                )
            )
        );
    }

    public function test_it_handles_organization_without_certificate(): void
    {
        $response = (object) [
            'id' => 'org_456',
            'is_production_ready' => false,
            'pending_steps' => [
                (object) [
                    'type' => 'certificate',
                ],
            ],
            'legal' => (object) [
                'name' => 'Sin certificado',
            ],
            'certificate' => (object) [
                'has_certificate' => false,
            ],
        ];

        $organization = (
            new FacturapiOrganizationMapper()
        )->fromResponse($response);

        self::assertFalse(
            $organization->productionReady
        );

        self::assertSame(
            ['certificate'],
            $organization->pendingSteps
        );

        self::assertFalse(
            $organization->certificateLoaded
        );

        self::assertNull(
            $organization->certificateExpiresAt
        );

        self::assertFalse(
            $organization->certificateExpired()
        );
    }
}