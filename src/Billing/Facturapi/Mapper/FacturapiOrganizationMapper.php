<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Facturapi\Mapper;

use DateTimeImmutable;
use SVR\Financial\Billing\Enums\BillingProvider;
use SVR\Financial\Billing\Models\BillingOrganization;

final class FacturapiOrganizationMapper
{
    public function fromResponse(
        object $organization,
    ): BillingOrganization {
        $pendingSteps = [];

        if (
            isset($organization->pending_steps)
            && is_array($organization->pending_steps)
        ) {
            foreach ($organization->pending_steps as $step) {
                if (is_object($step)) {
                    $pendingSteps[] = (string) (
                        $step->type
                        ?? $step->description
                        ?? 'unknown'
                    );
                }
            }
        }

        return new BillingOrganization(
            provider: BillingProvider::Facturapi,
            providerId: (string) $organization->id,
            name: isset($organization->legal->name)
                ? (string) $organization->legal->name
                : null,
            productionReady:
                (bool) ($organization->is_production_ready ?? false),
            pendingSteps: $pendingSteps,
            createdAt: isset($organization->created_at)
                ? new DateTimeImmutable(
                    (string) $organization->created_at
                )
                : null,
        );
    }
}