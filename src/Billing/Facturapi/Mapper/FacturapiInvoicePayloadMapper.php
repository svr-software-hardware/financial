<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Facturapi\Mapper;

use SVR\Financial\Billing\DTO\InvoiceData;
use SVR\Financial\Billing\DTO\InvoiceItemData;
use SVR\Financial\Billing\DTO\PublicGeneralInvoiceData;
use SVR\Financial\Billing\DTO\TaxData;

final class FacturapiInvoicePayloadMapper
{
    /**
     * @return array<string, mixed>
     */
    public function map(
        InvoiceData $data,
        string $customerId,
    ): array {
        return [
            'customer' => $customerId,
            'items' => $this->mapItems(
                $data->items
            ),
            'use' => $data->cfdiUsage,
            'payment_form' => $data->paymentForm,
            'payment_method' => $data->paymentMethod,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function mapPublicGeneral(
        PublicGeneralInvoiceData $data,
    ): array {
        return [
            'customer' => [
                'legal_name' => 'PUBLICO EN GENERAL',
                'tax_id' => 'XAXX010101000',
                'tax_system' => '616',
                'address' => [
                    'zip' => trim(
                        $data->issuerZipCode
                    ),
                    'country' => 'MEX',
                ],
            ],
            'items' => $this->mapItems(
                $data->items
            ),
            'use' => 'S01',
            'payment_form' => $data->paymentForm,
            'payment_method' => $data->paymentMethod,
            'global' => [
                'periodicity' => $data
                    ->periodicity
                    ->value,
                'months' => $data->months,
                'year' => $data->year,
            ],
        ];
    }

    /**
     * @param array<int, InvoiceItemData> $items
     *
     * @return array<int, array<string, mixed>>
     */
    private function mapItems(
        array $items,
    ): array {
        return array_map(
            fn (
                InvoiceItemData $item
            ): array => $this->mapItem($item),
            $items,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function mapItem(
        InvoiceItemData $item,
    ): array {
        $product = [
            'description' => $item->description,
            'product_key' => $item->productKey,
            'unit_key' => $item->unitKey,
            'price' => $item->price,
            'tax_included' => $item->taxIncluded,
            'taxes' => array_map(
                fn (TaxData $tax): array => [
                    'type' => $tax->type,
                    'rate' => $tax->rate,
                ],
                $item->taxes,
            ),
        ];

        $sku = trim((string) $item->sku);

        if ($sku !== '') {
            $product['sku'] = $sku;
        }

        return [
            'quantity' => $item->quantity,
            'discount' => $item->discount,
            'product' => $product,
        ];
    }
}