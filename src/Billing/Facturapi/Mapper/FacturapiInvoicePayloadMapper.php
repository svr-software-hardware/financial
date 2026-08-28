<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Facturapi\Mapper;

use SVR\Financial\Billing\DTO\InvoiceData;
use SVR\Financial\Billing\DTO\InvoiceItemData;
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
            'items' => array_map(
                fn (
                    InvoiceItemData $item
                ): array => $this->mapItem($item),
                $data->items,
            ),
            'use' => $data->cfdiUsage,
            'payment_form' => $data->paymentForm,
            'payment_method' => $data->paymentMethod,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapItem(
        InvoiceItemData $item,
    ): array {
        return [
            'quantity' => $item->quantity,
            'discount' => $item->discount,
            'product' => [
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
            ],
        ];
    }
}