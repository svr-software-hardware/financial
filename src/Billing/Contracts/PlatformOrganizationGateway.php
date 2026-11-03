<?php

declare(strict_types=1);
namespace SVR\Financial\Billing\Contracts;
use SVR\Financial\Billing\Models\BillingOrganization;
/** Reads the issuer authenticated by the principal API key; no User Key required. */
interface PlatformOrganizationGateway
{
    public function retrieve(): BillingOrganization;
}
