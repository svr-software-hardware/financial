<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\DTO;

use InvalidArgumentException;
use SVR\Financial\Billing\Enums\GlobalPeriodicity;

final readonly class PublicGeneralInvoiceData
{
    /**
     * @param array<int, InvoiceItemData> $items
     */
    public function __construct(
        public array $items,
        public string $paymentForm,
        public string $issuerZipCode,
        public GlobalPeriodicity $periodicity,
        public string $months,
        public int $year,
        public string $paymentMethod = 'PUE',
    ) {
        if ($this->items === []) {
            throw new InvalidArgumentException(
                'La factura a Público en General debe contener al menos un concepto.'
            );
        }

        foreach ($this->items as $item) {
            if (!$item instanceof InvoiceItemData) {
                throw new InvalidArgumentException(
                    'Todos los conceptos deben ser instancias de InvoiceItemData.'
                );
            }
        }

        if (!preg_match('/^\d{2}$/', trim($this->paymentForm))) {
            throw new InvalidArgumentException(
                'La forma de pago debe ser un código SAT de 2 dígitos.'
            );
        }

        if (!preg_match('/^\d{5}$/', trim($this->issuerZipCode))) {
            throw new InvalidArgumentException(
                'El código postal fiscal del emisor debe contener 5 dígitos.'
            );
        }

        if (!preg_match('/^\d{2}$/', $this->months)) {
            throw new InvalidArgumentException(
                'El mes o bimestre debe utilizar una clave de 2 dígitos.'
            );
        }

        $monthCode = (int) $this->months;

        if (
            $this->periodicity === GlobalPeriodicity::TwoMonths
            && ($monthCode < 13 || $monthCode > 18)
        ) {
            throw new InvalidArgumentException(
                'La periodicidad bimestral debe utilizar una clave entre 13 y 18.'
            );
        }

        if (
            $this->periodicity !== GlobalPeriodicity::TwoMonths
            && ($monthCode < 1 || $monthCode > 12)
        ) {
            throw new InvalidArgumentException(
                'La periodicidad seleccionada debe utilizar una clave de mes entre 01 y 12.'
            );
        }

        if ($this->year < 2000 || $this->year > 9999) {
            throw new InvalidArgumentException(
                'El año de la factura global no es válido.'
            );
        }

        if (
            $this->paymentMethod !== 'PUE'
            && $this->paymentMethod !== 'PPD'
        ) {
            throw new InvalidArgumentException(
                'El método de pago debe ser PUE o PPD.'
            );
        }
    }
}