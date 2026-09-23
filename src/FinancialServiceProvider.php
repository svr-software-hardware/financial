<?php

declare(strict_types=1);

namespace SVR\Financial;

use Illuminate\Support\ServiceProvider;
use SVR\Financial\Payments\OpenPay\OpenPayClientFactory;
use RuntimeException;
use SVR\Financial\Payments\Contracts\PaymentGateway;
use SVR\Financial\Payments\Enums\PaymentProvider;
use SVR\Financial\Payments\OpenPay\OpenPayGateway;
use SVR\Financial\Payments\PaymentManager;
use SVR\Financial\Billing\Facturapi\FacturapiClientFactory;
use SVR\Financial\Billing\BillingManager;
use SVR\Financial\Billing\Contracts\FiscalCustomerGateway;
use SVR\Financial\Billing\Contracts\InvoiceGateway;
use SVR\Financial\Billing\Facturapi\FacturapiFiscalCustomerGateway;
use SVR\Financial\Billing\Facturapi\FacturapiGateway;
use SVR\Financial\Billing\Contracts\OrganizationGateway;
use SVR\Financial\Billing\Facturapi\FacturapiOrganizationGateway;

final class FinancialServiceProvider extends ServiceProvider {
  public function register(): void {
    $this->mergeConfigFrom(
      __DIR__ . '/../config/financial.php',
      'financial'
    );

    $this->app->singleton(
      OpenPayClientFactory::class,
      function ($app): OpenPayClientFactory {
        $config = $app['config']->get(
          'financial.payments.openpay',
          []
        );

        return new OpenPayClientFactory(
          merchantId: $config['merchant_id'] ?? null,
          privateKey: $config['private_key'] ?? null,
          country: $config['country'] ?? 'MX',
          production: (bool) ($config['production'] ?? false),
          defaultCustomerIp: $config['public_ip'] ?? null,
        );
      }
    );

    $this->app->bind(
      PaymentGateway::class,
      function ($app): PaymentGateway {
        $configuredProvider = (string) $app['config']->get(
          'financial.payments.default',
          'openpay'
        );

        $provider = PaymentProvider::tryFrom(
          $configuredProvider
        );

        return match ($provider) {
          PaymentProvider::OpenPay
          => $app->make(OpenPayGateway::class),

          default => throw new RuntimeException(
            "El proveedor de pagos [{$configuredProvider}] no está soportado."
          ),
        };
      }
    );

    $this->app->singleton(
      PaymentManager::class,
      fn($app): PaymentManager => new PaymentManager(
        $app->make(PaymentGateway::class)
      )
    );

    $this->app->singleton(
      FacturapiClientFactory::class,
      function ($app): FacturapiClientFactory {
        $config = $app['config']->get(
          'financial.invoices.facturapi',
          []
        );

        return new FacturapiClientFactory(
          apiKey: $config['api_key'] ?? null,
          userKey: $config['user_key'] ?? null,
          production: (bool) (
            $config['production'] ?? false
          ),
        );
      }
    );

    $this->app->bind(
      InvoiceGateway::class,
      FacturapiGateway::class,
    );

    $this->app->bind(
      FiscalCustomerGateway::class,
      FacturapiFiscalCustomerGateway::class,
    );

    $this->app->singleton(
      BillingManager::class,
    );

    $this->app->bind(
      OrganizationGateway::class,
      FacturapiOrganizationGateway::class,
    );
  }

  public function boot(): void {
    $this->publishes(
      [
        __DIR__ . '/../config/financial.php'
        => config_path('financial.php'),
      ],
      'financial-config'
    );
  }
}