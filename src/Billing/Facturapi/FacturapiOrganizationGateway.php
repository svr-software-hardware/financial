<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Facturapi;

use InvalidArgumentException;
use SVR\Financial\Billing\Contracts\OrganizationGateway;
use SVR\Financial\Billing\DTO\OrganizationCertificateData;
use SVR\Financial\Billing\DTO\OrganizationData;
use SVR\Financial\Billing\DTO\OrganizationLegalData;
use SVR\Financial\Billing\Enums\BillingProvider;
use SVR\Financial\Billing\Exceptions\BillingProviderException;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiErrorMapper;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiOrganizationLegalMapper;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiOrganizationMapper;
use SVR\Financial\Billing\Models\BillingOrganization;
use Throwable;

final readonly class FacturapiOrganizationGateway implements OrganizationGateway
{
    public function __construct(
        private FacturapiClientFactory $clientFactory,
        private FacturapiOrganizationMapper $organizationMapper,
        private FacturapiOrganizationLegalMapper $organizationLegalMapper,
        private FacturapiErrorMapper $errorMapper,
    ) {
    }

    public function create(
        OrganizationData $data,
    ): BillingOrganization {
        try {
            $facturapi = $this->clientFactory
                ->createUserClient();

            $organization = $facturapi
                ->Organizations
                ->create([
                    'name' => trim($data->name),
                ]);

            return $this->organizationMapper
                ->fromResponse($organization);

        } catch (Throwable $error) {
            throw $this->providerException($error);
        }
    }

    public function updateLegalData(
        string $organizationId,
        OrganizationLegalData $data,
    ): BillingOrganization {
        $organizationId = $this->requireOrganizationId(
            $organizationId
        );

        try {
            $facturapi = $this->clientFactory
                ->createUserClient();

            $organization = $facturapi
                ->Organizations
                ->updateLegal(
                    $organizationId,
                    $this->organizationLegalMapper
                        ->toPayload($data),
                );

            return $this->organizationMapper
                ->fromResponse($organization);

        } catch (Throwable $error) {
            throw $this->providerException($error);
        }
    }

    public function uploadCertificate(
        string $organizationId,
        OrganizationCertificateData $data,
    ): BillingOrganization {
        $organizationId = $this->requireOrganizationId(
            $organizationId
        );

        try {
            $facturapi = $this->clientFactory
                ->createUserClient();

            $organization = $facturapi
                ->Organizations
                ->uploadCertificate(
                    $organizationId,
                    [
                        'cerFile' => $data->cerFile,
                        'keyFile' => $data->keyFile,
                        'password' => $data->password(),
                    ],
                );

            return $this->organizationMapper
                ->fromResponse($organization);

        } catch (Throwable $error) {
            throw $this->providerException($error);
        }
    }

    public function createLiveApiKey(
        string $organizationId,
    ): string {
        $organizationId = $this->requireOrganizationId(
            $organizationId
        );

        try {
            $facturapi = $this->clientFactory
                ->createUserClient();

            $apiKey = $facturapi
                ->Organizations
                ->renewLiveApiKey(
                    $organizationId
                );

            if (
                !is_string($apiKey)
                || trim($apiKey) === ''
            ) {
                throw new \RuntimeException(
                    'Facturapi no devolvió una Live API Key válida.'
                );
            }

            return trim($apiKey);

        } catch (Throwable $error) {
            throw $this->providerException($error);
        }
    }

    private function requireOrganizationId(
        string $organizationId,
    ): string {
        $organizationId = trim(
            $organizationId
        );

        if ($organizationId === '') {
            throw new InvalidArgumentException(
                'El ID de la organización no puede estar vacío.'
            );
        }

        return $organizationId;
    }

    private function providerException(
        Throwable $error,
    ): BillingProviderException {
        return new BillingProviderException(
            provider: BillingProvider::Facturapi,
            error: $this->errorMapper->map(
                $error
            ),
            previous: $error,
        );
    }
}