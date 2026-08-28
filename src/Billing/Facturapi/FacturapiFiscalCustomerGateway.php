<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Facturapi;

use SVR\Financial\Billing\Contracts\FiscalCustomerGateway;
use SVR\Financial\Billing\DTO\BillingContext;
use SVR\Financial\Billing\DTO\FiscalCustomerData;
use SVR\Financial\Billing\Exceptions\BillingProviderException;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiErrorMapper;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiFiscalCustomerMapper;
use SVR\Financial\Billing\Models\FiscalCustomer;
use SVR\Financial\Billing\Models\FiscalValidation;
use SVR\Financial\Billing\Enums\BillingProvider;
use Throwable;

final readonly class FacturapiFiscalCustomerGateway implements FiscalCustomerGateway
{
    public function __construct(
        private FacturapiClientFactory $clientFactory,
        private FacturapiFiscalCustomerMapper $customerMapper,
        private FacturapiErrorMapper $errorMapper,
    ) {
    }

    public function createCustomer(
        FiscalCustomerData $data,
        BillingContext $context,
    ): FiscalCustomer {
        try {
            $facturapi = $this->clientFactory->create(
                $context
            );

            $customer = $facturapi
                ->Customers
                ->create(
                    $this->customerMapper->toPayload(
                        $data
                    )
                );

            return $this->customerMapper
                ->fromResponse($customer);
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

    public function validateCustomerData(
        FiscalCustomerData $data,
        BillingContext $context,
    ): FiscalValidation {
        try {
            $customer = $this->createCustomer(
                $data,
                $context,
            );

            try {
                $this->deleteCustomer(
                    $customer->providerId,
                    $context,
                );
            } catch (Throwable) {
                /*
                 * La validación fiscal ya fue exitosa.
                 * La eliminación temporal no debe convertir
                 * una validación correcta en un error fiscal.
                 */
            }

            return new FiscalValidation(
                valid: true,
                providerCustomerId:
                    $customer->providerId,
            );
        } catch (BillingProviderException $error) {
            return new FiscalValidation(
                valid: false,
                message: $error->error->message,
            );
        }
    }

    public function deleteCustomer(
        string $providerCustomerId,
        BillingContext $context,
    ): void {
        $providerCustomerId = trim(
            $providerCustomerId
        );

        if ($providerCustomerId === '') {
            return;
        }

        try {
            $facturapi = $this->clientFactory->create(
                $context
            );

            $facturapi
                ->Customers
                ->delete(
                    $providerCustomerId
                );
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