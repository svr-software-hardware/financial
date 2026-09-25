# Facturación con Facturapi

El punto de entrada público es `BillingManager`:

```php
use SVR\Financial\Billing\BillingManager;

$billing = app(BillingManager::class);
```

La aplicación debe trabajar con el manager y los DTO públicos, no con las
clases internas de `Facturapi`.

## Datos fiscales del receptor

```php
use SVR\Financial\Billing\DTO\FiscalCustomerData;

$customerData = new FiscalCustomerData(
    taxId: 'ABC010101ABC',
    legalName: 'EMPRESA DEMO SA DE CV',
    taxSystem: '601',
    zipCode: '38000',
    country: 'MEX',
);
```

| Parámetro | Tipo | Predeterminado | Uso |
|---|---|---:|---|
| `taxId` | `string` | — | RFC del receptor. |
| `legalName` | `string` | — | Nombre o razón social fiscal. |
| `taxSystem` | `string` | — | Código SAT del régimen fiscal. |
| `zipCode` | `string` | — | Código postal fiscal. |
| `country` | `string` | `'MEX'` | Código de país enviado a Facturapi. |

Los valores deben ser códigos fiscales reales. Por ejemplo, debe enviarse
`$fiscalRegime->code`, no el ID interno de una tabla de la aplicación.

## Validar datos fiscales

```php
$validation = $billing->validateCustomer(
    $customerData
);
```

`validateCustomer()` crea temporalmente el cliente en Facturapi. Si la creación
es válida, intenta eliminarlo; un fallo al eliminar ese cliente temporal no
cambia una validación exitosa.

El resultado es `FiscalValidation`:

```php
$validation->valid;
$validation->message;
$validation->providerCustomerId;
$validation->error;
```

Cuando falla, `error` contiene el `BillingError` normalizado:

```php
if (!$validation->valid) {
    return response()->json([
        'message' => $validation->message,
        'category' => $validation->error?->category->value,
        'code' => $validation->error?->code,
        'path' => $validation->error?->path,
    ], 422);
}
```

La validación con una Organization acepta el mismo DTO y un
`BillingContext`; consulta [Organizations](organizations.md).

## Crear y eliminar clientes fiscales

```php
$customer = $billing->createCustomer($customerData);
```

El resultado es `FiscalCustomer`:

```php
$customer->provider;
$customer->providerId;
$customer->taxId;
$customer->legalName;
$customer->taxSystem;
$customer->zipCode;
$customer->validatedAt;
```

Para eliminarlo de Facturapi:

```php
$billing->deleteCustomer(
    $customer->providerId
);
```

`deleteCustomer()` no hace nada si recibe una cadena vacía.

## Impuestos

Cada impuesto se representa con `TaxData`:

```php
use SVR\Financial\Billing\DTO\TaxData;

$iva = new TaxData(
    type: 'IVA',
    rate: 0.16,
);
```

Sus parámetros son `type: string` y `rate: float`.

## Conceptos

```php
use SVR\Financial\Billing\DTO\InvoiceItemData;

$item = new InvoiceItemData(
    description: 'Servicio de correo electrónico',
    productKey: '81112100',
    unitKey: 'E48',
    price: 116.00,
    quantity: 1,
    discount: 0,
    taxIncluded: true,
    taxes: [$iva],
    sku: 'venta-123',
);
```

| Parámetro | Tipo | Predeterminado |
|---|---|---:|
| `description` | `string` | — |
| `productKey` | `string` | — |
| `unitKey` | `string` | — |
| `price` | `float` | — |
| `quantity` | `float` | `1` |
| `discount` | `float` | `0` |
| `taxIncluded` | `bool` | `true` |
| `taxes` | `array` | `[]` |
| `sku` | `?string` | `null` |

`taxes` debe contener instancias de `TaxData`. `sku` es opcional; cuando queda
vacío después de recortar espacios, no se envía a Facturapi.

## Construir la factura

```php
use SVR\Financial\Billing\DTO\InvoiceData;

$invoiceData = new InvoiceData(
    customer: $customerData,
    items: [$item],
    cfdiUsage: 'G03',
    paymentForm: '04',
    paymentMethod: 'PUE',
);
```

| Parámetro | Tipo | Predeterminado | Uso |
|---|---|---:|---|
| `customer` | `FiscalCustomerData` | — | Receptor del CFDI. |
| `items` | `array` | — | Conceptos `InvoiceItemData`. |
| `cfdiUsage` | `string` | — | Código SAT de uso CFDI. |
| `paymentForm` | `string` | — | Código SAT de forma de pago. |
| `paymentMethod` | `string` | `'PUE'` | Método de pago enviado a Facturapi. |

Estos DTO conservan los datos recibidos; salvo las validaciones expresamente
indicadas, la aplicación debe validar sus reglas y códigos SAT antes de llamar
al proveedor.

## Timbrar con la cuenta principal

```php
$invoice = $billing->stamp(
    $invoiceData
);
```

La librería crea primero el cliente fiscal en Facturapi y después genera la
factura con su identificador. Al omitir el contexto se utiliza
`BillingContext::default()` y `FACTURAPI_KEY`.

El resultado es `Invoice`:

```php
$invoice->provider;
$invoice->providerId;
$invoice->uuid;
$invoice->createdAt;
```

## Usar `BillingContext`

El contexto predeterminado representa la cuenta principal:

```php
use SVR\Financial\Billing\DTO\BillingContext;

$context = BillingContext::default();
```

Una Organization se selecciona con:

```php
$context = BillingContext::organization(
    $organizationId,
    $organizationLiveApiKey,
);

$invoice = $billing->stamp(
    $invoiceData,
    $context,
);
```

En sandbox puede omitirse la Live API Key; en producción es obligatoria. El
alta, datos fiscales, CSD y creación de la llave se explican en
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

Ambos métodos devuelven el contenido como `string`. Una factura emitida por una
Organization debe descargarse con el mismo contexto.

```php
return response($pdf, 200, [
    'Content-Type' => 'application/pdf',
]);
```

## Público en General

Las facturas globales usan `PublicGeneralInvoiceData` y
`BillingManager::stampPublicGeneral()`. Consulta
[Facturación a Público en General](public-general.md).

## Persistencia y reglas de negocio

SVR Financial no guarda clientes fiscales, facturas, PDF ni XML. La aplicación
debe evitar duplicados, definir qué se factura, persistir la relación con su
dominio y conservar al menos:

```php
$invoice->providerId;
$invoice->uuid;
```

Consulta [Manejo de errores](errors.md) para tratar fallos del proveedor.
