<?php
declare(strict_types=1);
namespace SVR\Financial\Tests\Billing\Facturapi;
use PHPUnit\Framework\TestCase;
use SVR\Financial\Billing\Facturapi\FacturapiPlatformOrganizationGateway;
use SVR\Financial\Billing\Facturapi\FacturapiClientFactory;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiOrganizationMapper;
use SVR\Financial\Billing\Facturapi\Mapper\FacturapiErrorMapper;
use SVR\Financial\Billing\Exceptions\BillingProviderException;
use SVR\Financial\Billing\Enums\BillingErrorCategory;
final class FacturapiPlatformOrganizationGatewayTest extends TestCase
{
    public function test_a_user_key_does_not_replace_the_principal_issuer_key(): void
    {
        $gateway=new FacturapiPlatformOrganizationGateway(new FacturapiClientFactory(null,'user-key'),new FacturapiOrganizationMapper(),new FacturapiErrorMapper());
        try { $gateway->retrieve();self::fail('Missing principal API key'); }
        catch(BillingProviderException $error) {
            self::assertSame(BillingErrorCategory::Configuration,$error->error->category);
            self::assertStringContainsString('API Key principal',$error->error->providerMessage);
        }
    }
}
