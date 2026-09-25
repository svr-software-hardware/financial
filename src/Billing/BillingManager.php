<?php

declare(strict_types=1);

namespace SVR\Financial\Billing;

use SVR\Financial\Billing\Contracts\FiscalCustomerGateway;
use SVR\Financial\Billing\Contracts\InvoiceGateway;
use SVR\Financial\Billing\Contracts\OrganizationGateway;
use SVR\Financial\Billing\DTO\BillingContext;
use SVR\Financial\Billing\DTO\FiscalCustomerData;
use SVR\Financial\Billing\DTO\InvoiceData;
use SVR\Financial\Billing\DTO\OrganizationCertificateData;
use SVR\Financial\Billing\DTO\OrganizationData;
use SVR\Financial\Billing\DTO\OrganizationLegalData;
use SVR\Financial\Billing\DTO\PublicGeneralInvoiceData;
use SVR\Financial\Billing\Enums\BillingProvider;
use SVR\Financial\Billing\Models\BillingOrganization;
use SVR\Financial\Billing\Models\FiscalCustomer;
use SVR\Financial\Billing\Models\FiscalValidation;
use SVR\Financial\Billing\Models\Invoice;

final readonly class BillingManager {
    public function __construct(
        private InvoiceGateway $invoiceGateway,
        private FiscalCustomerGateway $customerGateway,
        private OrganizationGateway $organizationGateway,
    ) {
    }

    public function provider(): BillingProvider {
        return $this->invoiceGateway
            ->provider();
    }

    public function stamp(
        InvoiceData $data,
        ?BillingContext $context = null,
    ): Invoice {
        return $this->invoiceGateway
            ->stamp(
                $data,
                $context ?? BillingContext::default(),
            );
    }

    public function stampPublicGeneral(
        PublicGeneralInvoiceData $data,
        ?BillingContext $context = null,
    ): Invoice {
        return $this->invoiceGateway
            ->stampPublicGeneral(
                $data,
                $context ?? BillingContext::default(),
            );
    }

    public function downloadPdf(
        string $providerId,
        ?BillingContext $context = null,
    ): string {
        return $this->invoiceGateway
            ->downloadPdf(
                $providerId,
                $context ?? BillingContext::default(),
            );
    }

    public function downloadXml(
        string $providerId,
        ?BillingContext $context = null,
    ): string {
        return $this->invoiceGateway
            ->downloadXml(
                $providerId,
                $context ?? BillingContext::default(),
            );
    }

    public function createCustomer(
        FiscalCustomerData $data,
        ?BillingContext $context = null,
    ): FiscalCustomer {
        return $this->customerGateway
            ->createCustomer(
                $data,
                $context ?? BillingContext::default(),
            );
    }

    public function validateCustomer(
        FiscalCustomerData $data,
        ?BillingContext $context = null,
    ): FiscalValidation {
        return $this->customerGateway
            ->validateCustomerData(
                $data,
                $context ?? BillingContext::default(),
            );
    }

    public function deleteCustomer(
        string $providerCustomerId,
        ?BillingContext $context = null,
    ): void {
        $this->customerGateway
            ->deleteCustomer(
                $providerCustomerId,
                $context ?? BillingContext::default(),
            );
    }

    public function createOrganization(
        OrganizationData $data,
    ): BillingOrganization {
        return $this->organizationGateway
            ->create($data);
    }

    public function updateOrganizationLegalData(
        string $organizationId,
        OrganizationLegalData $data,
    ): BillingOrganization {
        return $this->organizationGateway
            ->updateLegalData(
                $organizationId,
                $data,
            );
    }

    public function uploadOrganizationCertificate(
        string $organizationId,
        OrganizationCertificateData $data,
    ): BillingOrganization {
        return $this->organizationGateway
            ->uploadCertificate(
                $organizationId,
                $data,
            );
    }

    public function createOrganizationLiveApiKey(
        string $organizationId,
    ): string {
        return $this->organizationGateway
            ->createLiveApiKey(
                $organizationId
            );
    }
}