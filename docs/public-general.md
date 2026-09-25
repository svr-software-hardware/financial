# Facturación a Público en General

SVR Financial genera facturas globales de Facturapi mediante
`BillingManager::stampPublicGeneral()`. La librería fija automáticamente los
datos fiscales de Público en General:

```text
RFC: XAXX010101000
Razón social: PUBLICO EN GENERAL
Régimen fiscal: 616
Uso CFDI: S01
País: MEX
```

La aplicación consumidora debe proporcionar los conceptos, la forma y método
de pago, el código postal fiscal del emisor y el periodo global.

## Resolver el manager

```php
use SVR\Financial\Billing\BillingManager;

$billing = app(BillingManager::class);
```

## Construir los conceptos

Cada concepto se representa con `InvoiceItemData`:

```php
use SVR\Financial\Billing\DTO\InvoiceItemData;
use SVR\Financial\Billing\DTO\TaxData;

$item = new InvoiceItemData(
    description: 'Servicios de correo electrónico del periodo',
    productKey: '81112100',
    unitKey: 'E48',
    price: 116.00,
    quantity: 1,
    discount: 0,
    taxIncluded: true,
    taxes: [
        new TaxData(
            type: 'IVA',
            rate: 0.16,
        ),
    ],
    sku: 'venta-2026-09',
);
```

Parámetros de `InvoiceItemData`:

| Parámetro | Tipo | Valor predeterminado | Uso |
|---|---|---:|---|
| `description` | `string` | — | Descripción del concepto. |
| `productKey` | `string` | — | Clave de producto o servicio del SAT. |
| `unitKey` | `string` | — | Clave de unidad del SAT. |
| `price` | `float` | — | Precio unitario. |
| `quantity` | `float` | `1` | Cantidad facturada. |
| `discount` | `float` | `0` | Descuento del concepto. |
| `taxIncluded` | `bool` | `true` | Indica si el precio incluye impuestos. |
| `taxes` | `array` | `[]` | Instancias de `TaxData`. |
| `sku` | `?string` | `null` | Referencia propia de la aplicación. |

`sku` es opcional. La librería elimina espacios en sus extremos y no lo envía
a Facturapi cuando es `null`, una cadena vacía o sólo contiene espacios.

La aplicación debe decidir cómo agrupar sus operaciones y construir uno o más
conceptos. SVR Financial no consulta ventas ni calcula importes acumulados.

## Periodicidad global

La periodicidad se representa con `GlobalPeriodicity`:

```php
use SVR\Financial\Billing\Enums\GlobalPeriodicity;
```

| Caso | Valor enviado a Facturapi |
|---|---|
| `GlobalPeriodicity::Day` | `day` |
| `GlobalPeriodicity::Week` | `week` |
| `GlobalPeriodicity::Fortnight` | `fortnight` |
| `GlobalPeriodicity::Month` | `month` |
| `GlobalPeriodicity::TwoMonths` | `two_months` |

## Construir `PublicGeneralInvoiceData`

```php
use SVR\Financial\Billing\DTO\PublicGeneralInvoiceData;
use SVR\Financial\Billing\Enums\GlobalPeriodicity;

$invoiceData = new PublicGeneralInvoiceData(
    items: [$item],
    paymentForm: '04',
    issuerZipCode: '38000',
    periodicity: GlobalPeriodicity::Month,
    months: '09',
    year: 2026,
    paymentMethod: 'PUE',
);
```

Parámetros:

| Parámetro | Tipo | Valor predeterminado | Regla aplicada por la librería |
|---|---|---:|---|
| `items` | `array` | — | Al menos un `InvoiceItemData`. |
| `paymentForm` | `string` | — | Código SAT de dos dígitos. |
| `issuerZipCode` | `string` | — | Código postal fiscal del emisor, exactamente cinco dígitos. |
| `periodicity` | `GlobalPeriodicity` | — | Periodicidad de la factura global. |
| `months` | `string` | — | Clave de mes o bimestre de dos dígitos. |
| `year` | `int` | — | Año entre `2000` y `9999`. |
| `paymentMethod` | `string` | `'PUE'` | Sólo acepta `PUE` o `PPD`. |

Para `Day`, `Week`, `Fortnight` y `Month`, `months` debe estar entre `01` y
`12`. Para `TwoMonths`, debe utilizar una clave bimestral entre `13` y `18`.

## Timbrar con la cuenta principal

```php
$invoice = $billing->stampPublicGeneral(
    $invoiceData
);
```

Al omitir el segundo argumento se utiliza `BillingContext::default()` y la
credencial `FACTURAPI_KEY`.

El resultado es una instancia de `SVR\Financial\Billing\Models\Invoice`:

```php
$invoice->provider;
$invoice->providerId;
$invoice->uuid;
$invoice->createdAt;
```

## Timbrar con una Organization

En sandbox:

```php
use SVR\Financial\Billing\DTO\BillingContext;

$context = BillingContext::organization(
    $organizationId
);

$invoice = $billing->stampPublicGeneral(
    $invoiceData,
    $context,
);
```

En producción debe entregarse la Live API Key de la Organization:

```php
$context = BillingContext::organization(
    $organizationId,
    $organizationLiveApiKey,
);

$invoice = $billing->stampPublicGeneral(
    $invoiceData,
    $context,
);
```

La creación y preparación de Organizations se explica en
[Organizations](organizations.md).

## Descargar PDF y XML

Cuenta principal:

```php
$pdf = $billing->downloadPdf($invoice->providerId);
$xml = $billing->downloadXml($invoice->providerId);
```

Organization:

```php
$pdf = $billing->downloadPdf(
    $invoice->providerId,
    $context,
);

$xml = $billing->downloadXml(
    $invoice->providerId,
    $context,
);
```

Ambos métodos devuelven el contenido del archivo como `string`. Para una
factura emitida por una Organization se debe reutilizar el mismo contexto al
descargarla.

Ejemplo de respuesta Laravel:

```php
return response($pdf, 200, [
    'Content-Type' => 'application/pdf',
]);
```

## Responsabilidad de la aplicación

La aplicación consumidora debe:

- determinar qué operaciones forman parte de la factura global;
- calcular importes, descuentos, cantidades e impuestos;
- elegir claves SAT, periodicidad, mes o bimestre y año correctos;
- proporcionar el código postal fiscal del emisor;
- evitar timbrados duplicados;
- persistir `providerId`, `uuid` y la relación con sus operaciones;
- proteger la Live API Key cuando utilice una Organization;
- definir cuándo y dónde conservar o entregar PDF y XML.

Los errores de Facturapi se documentan en [Manejo de errores](errors.md).
