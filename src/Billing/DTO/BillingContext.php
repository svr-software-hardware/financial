<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\DTO;

final readonly class BillingContext
{
    private function __construct(
        public ?string $organizationId,
        public ?string $organizationApiKey,
    ) {
    }

    public static function default(): self
    {
        return new self(
            organizationId: null,
            organizationApiKey: null,
        );
    }

    public static function organization(
        string $organizationId,
        ?string $organizationApiKey = null,
    ): self {
        $organizationId = trim($organizationId);

        if ($organizationId === '') {
            throw new \InvalidArgumentException(
                'El ID de la organización no puede estar vacío.'
            );
        }

        return new self(
            organizationId: $organizationId,
            organizationApiKey: $organizationApiKey !== null
                ? trim($organizationApiKey)
                : null,
        );
    }

    public function usesOrganization(): bool
    {
        return $this->organizationId !== null;
    }
}