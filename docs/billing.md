# Facturación con Facturapi

El acceso público al módulo de facturación se realiza mediante:

```php
SVR\Financial\Billing\BillingManager
```

No se recomienda utilizar directamente:

```php
FacturapiGateway
FacturapiFiscalCustomerGateway
FacturapiClientFactory
```

---

# Datos fiscales

```php
use SVR\Financial\Billing\DTO\FiscalCustomerData;

$customerData = new FiscalCustomerData(
    taxId: 'RFC',
    legalName: 'NOMBRE O RAZÓN SOCIAL',
    taxSystem: '612',
    zipCode: '38000',
);
```

SVR Financial recibe códigos fiscales reales.

Por ejemplo:

```php
taxSystem: '612'
```

No debe recibir el ID interno de una tabla del proyecto.

Incorrecto:

```php
taxSystem: $fiscalRegime->id
```

Correcto:

```php
taxSystem: $fiscalRegime->code
```

---

# Validar datos fiscales

```php
use SVR\Financial\Billing\BillingManager;

$billing = app(BillingManager::class);

$validation = $billing->validateCustomer(
    $customerData
);
```

Resultado:

```php
$validation->valid;
$validation->message;
$validation->providerCustomerId;
$validation->error;
```

Cuando la validación falla, `error` conserva el `BillingError` completo con la
categoría, código, estado HTTP, path, log ID y mensaje original del proveedor.

Ejemplo:

```php
if (!$validation->valid) {
    return response()->json([
        'message' => $validation->message,
    ], 422);
}
```

---

# Crear cliente fiscal

```php
$customer = $billing->createCustomer(
    $customerData
);
```

Resultado:

```php
$customer->provider;
$customer->providerId;
$customer->taxId;
$customer->legalName;
$customer->taxSystem;
$customer->zipCode;
$customer->validatedAt;
```

---

# Impuestos

```php
use SVR\Financial\Billing\DTO\TaxData;

$iva = new TaxData(
    type: 'IVA',
    rate: 0.16,
);
```

---

# Conceptos

```php
use SVR\Financial\Billing\DTO\InvoiceItemData;

$item = new InvoiceItemData(
    description: 'Servicio',
    productKey: '85121600',
    unitKey: 'E48',
    price: 100.00,
    quantity: 1,
    discount: 0,
    taxIncluded: true,
    taxes: [
        $iva,
    ],
);
```

---

# Crear InvoiceData

```php
use SVR\Financial\Billing\DTO\InvoiceData;

$invoiceData = new InvoiceData(
    customer: $customerData,
    items: [
        $item,
    ],
    cfdiUsage: 'G03',
    paymentForm: '04',
    paymentMethod: 'PUE',
);
```

---

# Timbrar con cuenta principal

```php
$invoice = $billing->stamp(
    $invoiceData
);
```

Equivale a utilizar:

```php
BillingContext::default()
```

Resultado:

```php
$invoice->provider;
$invoice->providerId;
$invoice->uuid;
$invoice->createdAt;
```

---

# Organization

Para facturar mediante una Organization:

```php
use SVR\Financial\Billing\DTO\BillingContext;

$context = BillingContext::organization(
    $organizationId
);

$invoice = $billing->stamp(
    $invoiceData,
    $context
);
```

La misma `InvoiceData` puede utilizarse tanto para cuenta principal como para
Organization.

---

# Organization en producción

```php
$context = BillingContext::organization(
    $organizationId,
    $organizationLiveApiKey,
);
```

Después:

```php
$invoice = $billing->stamp(
    $invoiceData,
    $context
);
```

La aplicación debe obtener y proteger la Live API Key.

---

# Descargar PDF

Cuenta principal:

```php
$pdf = $billing->downloadPdf(
    $invoice->providerId
);
```

Organization:

```php
$pdf = $billing->downloadPdf(
    $invoice->providerId,
    $context
);
```

El resultado es el contenido del PDF.

Ejemplo Laravel:

```php
return response(
    $pdf,
    200,
    [
        'Content-Type' => 'application/pdf',
    ]
);
```

---

# Descargar XML

```php
$xml = $billing->downloadXml(
    $invoice->providerId,
    $context
);
```

Ejemplo:

```php
return response(
    $xml,
    200,
    [
        'Content-Type' => 'application/xml',
    ]
);
```

---

# Facturación dividida

SVR Financial no decide qué porcentaje o concepto corresponde a cada emisor.

Una aplicación puede realizar:

```text
Venta
 ├── Factura cuenta principal
 └── Factura Organization
```

La aplicación calcula los importes y crea dos `InvoiceData`.

Ejemplo conceptual:

```php
$mainInvoice = $billing->stamp(
    $mainInvoiceData
);

$organizationInvoice = $billing->stamp(
    $organizationInvoiceData,
    BillingContext::organization(
        $organizationId
    ),
);
```

---

# Persistencia

SVR Financial no persiste:

```text
facturas
ventas
clientes fiscales
organizations
PDF
XML
```

La aplicación decide qué información almacenar.

Normalmente será útil conservar:

```php
$invoice->providerId;
$invoice->uuid;
```
