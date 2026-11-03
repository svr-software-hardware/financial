<?php

declare(strict_types=1);
namespace SVR\Financial\Billing\Facturapi;
use SVR\Financial\Billing\Contracts\PlatformOrganizationGateway;
use SVR\Financial\Billing\DTO\BillingContext;
use SVR\Financial\Billing\Enums\BillingProvider;
use SVR\Financial\Billing\Exceptions\BillingProviderException;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiErrorMapper;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiOrganizationMapper;
use SVR\Financial\Billing\Models\BillingOrganization;
use Throwable;
final readonly class FacturapiPlatformOrganizationGateway implements PlatformOrganizationGateway
{
    public function __construct(private FacturapiClientFactory $clientFactory,private FacturapiOrganizationMapper $mapper,private FacturapiErrorMapper $errors) {}
    public function retrieve(): BillingOrganization
    {
        try {
            $client=$this->clientFactory->create(BillingContext::default());
            return $this->mapper->fromResponse($client->Organizations->retrieve('me'));
        } catch (Throwable $error) {
            throw new BillingProviderException(BillingProvider::Facturapi,$this->errors->map($error),$error);
        }
    }
}
