# SVR Financial

Librería interna para integrar pagos con OpenPay y facturación con Facturapi en
aplicaciones Laravel. Expone DTOs, modelos normalizados y dos puntos de entrada:

```php
SVR\Financial\Payments\PaymentManager
SVR\Financial\Billing\BillingManager
```

La aplicación consumidora mantiene sus reglas de negocio, persistencia,
idempotencia y relación entre pagos, facturas y entidades propias.

## Capacidades

### OpenPay

- cobros con tarjeta;
- 3D Secure;
- uso de puntos;
- consulta de cargos;
- teléfono del cliente opcional;
- estados, tarjetas y errores normalizados;
- separación entre rechazos bancarios y errores técnicos.

### Facturapi

- creación y validación de clientes fiscales;
- timbrado CFDI y descarga PDF/XML;
- cuenta principal y Organizations;
- creación y consulta de Organizations;
- estado de producción, pasos pendientes y estado del CSD;
- detección de expiración del certificado;
- actualización de datos fiscales;
- carga de CSD;
- creación de Live API Keys;
- facturación global a Público en General;
- contexto explícito de emisor mediante `BillingContext`.

## Requisitos

- PHP 8.4 o posterior;
- Laravel 12.x;
- Composer.

## Instalación rápida

Agregue el repositorio VCS al `composer.json` de la aplicación:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/svr-software-hardware/financial.git"
        }
    ]
}
```

```bash
composer require svr/financial:^1.0
php artisan vendor:publish --tag=financial-config
php artisan optimize:clear
```

Configure las credenciales en `.env`. Consulte la
[guía de instalación](docs/installation.md) para repositorios privados y uso
local mediante Composer `path`.

## Ejemplo mínimo de OpenPay

```php
use SVR\Financial\Payments\DTO\CardChargeData;
use SVR\Financial\Payments\DTO\CustomerData;
use SVR\Financial\Payments\OpenPay\DTO\OpenPayChargeOptions;
use SVR\Financial\Payments\PaymentManager;

$payments = app(PaymentManager::class);

$charge = $payments->charge(
    new CardChargeData(
        amount: 100.00,
        description: 'Servicio de correo electrónico',
        paymentMethodId: $tokenId,
        customer: new CustomerData(
            name: 'Juan',
            lastName: 'Pérez',
            email: 'juan@example.com',
        ),
        providerOptions: new OpenPayChargeOptions(
            deviceSessionId: $deviceSessionId,
            customerIp: $request->ip(),
        ),
    )
);

if ($charge->successful()) {
    $providerId = $charge->providerId;
}
```

El token de tarjeta y `deviceSessionId` deben obtenerse con las herramientas de
OpenPay en la aplicación consumidora.

## Ejemplo mínimo de Facturapi

```php
use SVR\Financial\Billing\BillingManager;
use SVR\Financial\Billing\DTO\FiscalCustomerData;
use SVR\Financial\Billing\DTO\InvoiceData;
use SVR\Financial\Billing\DTO\InvoiceItemData;
use SVR\Financial\Billing\DTO\TaxData;

$billing = app(BillingManager::class);

$customer = new FiscalCustomerData(
    taxId: 'ABC010101ABC',
    legalName: 'EMPRESA DEMO SA DE CV',
    taxSystem: '601',
    zipCode: '38000',
);

$invoice = $billing->stamp(
    new InvoiceData(
        customer: $customer,
        items: [
            new InvoiceItemData(
                description: 'Servicio de correo electrónico',
                productKey: '81112100',
                unitKey: 'E48',
                price: 116.00,
                taxes: [
                    new TaxData(
                        type: 'IVA',
                        rate: 0.16,
                    ),
                ],
            ),
        ],
        cfdiUsage: 'G03',
        paymentForm: '04',
    )
);

$providerId = $invoice->providerId;
$uuid = $invoice->uuid;
```

Para una factura global a Público en General utilice
`PublicGeneralInvoiceData` y `stampPublicGeneral()` en lugar de construir un
`InvoiceData` normal.

## Documentación

- [Instalación](docs/installation.md)
- [Configuración](docs/configuration.md)
- [Pagos con OpenPay](docs/payments.md)
- [Facturación con Facturapi](docs/billing.md)
- [Organizations](docs/organizations.md)
- [Facturación a Público en General](docs/public-general.md)
- [Manejo de errores](docs/errors.md)

## Límites de responsabilidad

SVR Financial no crea tablas ni persiste pagos, clientes fiscales, facturas,
Organizations, PDF o XML. Tampoco decide precios, conceptos, impuestos,
división entre emisores ni cuándo una operación debe reintentarse. Esas
decisiones pertenecen a cada aplicación consumidora.


## Identidad fiscal de la plataforma

La revisión prevista para Financial 1.2 agrega `PlatformOrganizationGateway::retrieve()`. Usa la clave principal de `financial.invoices.facturapi.api_key` para consultar `GET /organizations/me`; no utiliza User Secret Key ni requiere configurar un ID de organización de la plataforma.

```php
$organization = app(\SVR\Financial\Billing\Contracts\PlatformOrganizationGateway::class)->retrieve();
```

`BillingOrganization` incorpora propiedades opcionales `taxId`, `legalName`, `taxSystem`, `zipCode` y `country`, tomadas de `legal` y `legal.address`. El código postal se conserva como cadena y no se confunde el nombre comercial con la razón social fiscal. Los constructores anteriores siguen siendo compatibles porque los campos nuevos son opcionales y se agregaron al final.

La lectura es una consulta al proveedor y puede lanzar `BillingProviderException`. No modifica organizaciones ni certificados, ni emite una factura. El consumidor debe validar que la respuesta fiscal esté completa antes de utilizarla. Workflow realiza esa validación y conserva el snapshot utilizado en cada factura.

Referencia: [detalle de la organización autenticada](https://docs.facturapi.io/api/#tag/Organizaciones). Esta revisión todavía necesita su versionado/publicación; no se crearon commits ni tags durante su desarrollo.
