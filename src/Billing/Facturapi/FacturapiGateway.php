<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Facturapi;

use SVR\Financial\Billing\Contracts\InvoiceGateway;
use SVR\Financial\Billing\DTO\BillingContext;
use SVR\Financial\Billing\DTO\InvoiceData;
use SVR\Financial\Billing\Enums\BillingProvider;
use SVR\Financial\Billing\Exceptions\BillingProviderException;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiErrorMapper;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiInvoiceMapper;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiInvoicePayloadMapper;
use SVR\Financial\Billing\Models\Invoice;
use Throwable;

final readonly class FacturapiGateway implements InvoiceGateway
{
    public function __construct(
        private FacturapiClientFactory $clientFactory,
        private FacturapiFiscalCustomerGateway $customerGateway,
        private FacturapiInvoicePayloadMapper $payloadMapper,
        private FacturapiInvoiceMapper $invoiceMapper,
        private FacturapiErrorMapper $errorMapper,
    ) {
    }

    public function provider(): BillingProvider
    {
        return BillingProvider::Facturapi;
    }

    public function stamp(
        InvoiceData $data,
        BillingContext $context,
    ): Invoice {
        try {
            $customer = $this->customerGateway
                ->createCustomer(
                    $data->customer,
                    $context,
                );

            $facturapi = $this->clientFactory
                ->create($context);

            $invoice = $facturapi
                ->Invoices
                ->create(
                    $this->payloadMapper->map(
                        $data,
                        $customer->providerId,
                    )
                );

            return $this->invoiceMapper
                ->fromResponse($invoice);
        } catch (BillingProviderException $error) {
            throw $error;
        } catch (Throwable $error) {
            throw new BillingProviderException(
                provider: BillingProvider::Facturapi,
                error: $this->errorMapper->map(
                    $error
                ),
                previous: $error,
            );
        }
    }

    public function downloadPdf(
        string $providerId,
        BillingContext $context,
    ): string {
        return $this->download(
            $providerId,
            $context,
            'pdf',
        );
    }

    public function downloadXml(
        string $providerId,
        BillingContext $context,
    ): string {
        return $this->download(
            $providerId,
            $context,
            'xml',
        );
    }

    private function download(
        string $providerId,
        BillingContext $context,
        string $format,
    ): string {
        try {
            $facturapi = $this->clientFactory
                ->create($context);

            $content = match ($format) {
                'pdf' => $facturapi
                    ->Invoices
                    ->downloadPdf($providerId),

                'xml' => $facturapi
                    ->Invoices
                    ->downloadXml($providerId),
            };

            if (is_resource($content)) {
                $content = stream_get_contents(
                    $content
                );
            }

            if (!is_string($content)) {
                throw new \RuntimeException(
                    'Facturapi no devolvió un archivo válido.'
                );
            }

            return $content;
        } catch (Throwable $error) {
            throw new BillingProviderException(
                provider: BillingProvider::Facturapi,
                error: $this->errorMapper->map(
                    $error
                ),
                previous: $error,
            );
        }
    }
}