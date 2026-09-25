<?php

declare(strict_types=1);

namespace SVR\Financial\Tests\Billing;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SVR\Financial\Billing\DTO\InvoiceItemData;
use SVR\Financial\Billing\DTO\PublicGeneralInvoiceData;
use SVR\Financial\Billing\Enums\GlobalPeriodicity;

final class PublicGeneralInvoiceDataTest extends TestCase
{
    public function test_monthly_period_accepts_regular_month(): void
    {
        $data = new PublicGeneralInvoiceData(
            items: [$this->item()],
            paymentForm: '04',
            issuerZipCode: '38000',
            periodicity: GlobalPeriodicity::Month,
            months: '09',
            year: 2026,
        );

        self::assertSame(GlobalPeriodicity::Month, $data->periodicity);
        self::assertSame('09', $data->months);
        self::assertSame(2026, $data->year);
    }

    public function test_two_month_period_accepts_bimester_code(): void
    {
        $data = new PublicGeneralInvoiceData(
            items: [$this->item()],
            paymentForm: '04',
            issuerZipCode: '38000',
            periodicity: GlobalPeriodicity::TwoMonths,
            months: '17',
            year: 2026,
        );

        self::assertSame('17', $data->months);
    }

    public function test_regular_period_rejects_bimester_code(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'La periodicidad seleccionada debe utilizar una clave de mes entre 01 y 12.'
        );

        new PublicGeneralInvoiceData(
            items: [$this->item()],
            paymentForm: '04',
            issuerZipCode: '38000',
            periodicity: GlobalPeriodicity::Day,
            months: '13',
            year: 2026,
        );
    }

    public function test_two_month_period_rejects_regular_month_code(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'La periodicidad bimestral debe utilizar una clave entre 13 y 18.'
        );

        new PublicGeneralInvoiceData(
            items: [$this->item()],
            paymentForm: '04',
            issuerZipCode: '38000',
            periodicity: GlobalPeriodicity::TwoMonths,
            months: '09',
            year: 2026,
        );
    }

    public function test_it_rejects_an_invalid_issuer_zip_code(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'El código postal fiscal del emisor debe contener 5 dígitos.'
        );

        new PublicGeneralInvoiceData(
            items: [$this->item()],
            paymentForm: '04',
            issuerZipCode: '3800A',
            periodicity: GlobalPeriodicity::Month,
            months: '09',
            year: 2026,
        );
    }

    private function item(): InvoiceItemData
    {
        return new InvoiceItemData(
            description: 'Servicio de prueba',
            productKey: '01010101',
            unitKey: 'H87',
            price: 100.00,
        );
    }
}