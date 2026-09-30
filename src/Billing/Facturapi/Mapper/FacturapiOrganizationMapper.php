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

        $certificate = isset($organization->certificate)
            && is_object($organization->certificate)
                ? $organization->certificate
                : null;

        return new BillingOrganization(
            provider: BillingProvider::Facturapi,
            providerId: (string) $organization->id,
            name: isset($organization->legal->name)
                ? (string) $organization->legal->name
                : null,
            productionReady:
                (bool) ($organization->is_production_ready ?? false),
            pendingSteps: $pendingSteps,
            createdAt: $this->date(
                $organization->created_at ?? null
            ),
            certificateLoaded:
                (bool) ($certificate->has_certificate ?? false),
            certificateUpdatedAt: $this->date(
                $certificate->updated_at ?? null
            ),
            certificateExpiresAt: $this->date(
                $certificate->expires_at ?? null
            ),
            certificateSerialNumber:
                isset($certificate->serial_number)
                    ? (string) $certificate->serial_number
                    : null,
        );
    }

    private function date(
        mixed $value,
    ): ?DateTimeImmutable {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return new DateTimeImmutable($value);
    }
}