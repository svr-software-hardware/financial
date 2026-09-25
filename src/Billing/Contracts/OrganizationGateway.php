<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Contracts;

use SVR\Financial\Billing\DTO\OrganizationCertificateData;
use SVR\Financial\Billing\DTO\OrganizationData;
use SVR\Financial\Billing\DTO\OrganizationLegalData;
use SVR\Financial\Billing\Models\BillingOrganization;

interface OrganizationGateway
{
    public function create(
        OrganizationData $data,
    ): BillingOrganization;

    public function updateLegalData(
        string $organizationId,
        OrganizationLegalData $data,
    ): BillingOrganization;

    public function uploadCertificate(
        string $organizationId,
        OrganizationCertificateData $data,
    ): BillingOrganization;

    public function createLiveApiKey(
        string $organizationId,
    ): string;
}