# Organizations

SVR Financial permite crear y configurar Organizations de Facturapi para que
distintos emisores puedan generar sus propios CFDI.

Todas las operaciones se realizan mediante:

```php
use SVR\Financial\Billing\BillingManager;

$billing = app(BillingManager::class);
```

---

## Requisitos

Para administrar Organizations se requiere configurar:

```dotenv
FACTURAPI_USER_KEY=
```

En producción también debe utilizarse:

```dotenv
FACTURAPI_PRODUCTION=true
```

La configuración completa se encuentra en:

```text
config/financial.php
```

---

## Crear una Organization

Para crear una Organization se utiliza:

```php
use SVR\Financial\Billing\DTO\OrganizationData;

$data = new OrganizationData(
    name: 'Distribuidor Demo',
);

$organization = $billing->createOrganization(
    $data
);
```

El resultado es una instancia de:

```text
SVR\Financial\Billing\Models\BillingOrganization
```

Propiedades disponibles:

```php
$organization->provider;
$organization->providerId;
$organization->name;
$organization->productionReady;
$organization->pendingSteps;
$organization->createdAt;
```

El identificador de la Organization en Facturapi se encuentra en:

```php
$organization->providerId;
```

Este valor debe guardarse en la aplicación para utilizarlo posteriormente.

Ejemplo:

```php
$organizationId = $organization->providerId;
```

---

## Configurar datos fiscales

Los datos fiscales de una Organization se configuran mediante:

```text
OrganizationLegalData
OrganizationAddressData
```

Ejemplo:

```php
use SVR\Financial\Billing\DTO\OrganizationAddressData;
use SVR\Financial\Billing\DTO\OrganizationLegalData;

$address = new OrganizationAddressData(
    zip: '38000',
    street: 'Hidalgo',
    exterior: '10',
    interior: null,
    neighborhood: 'Centro',
    city: 'Celaya',
    municipality: 'Celaya',
    state: 'Guanajuato',
);

$legalData = new OrganizationLegalData(
    name: 'Distribuidor Demo',
    legalName: 'DISTRIBUIDOR DEMO SA DE CV',
    taxSystem: '601',
    address: $address,
    website: 'https://example.com',
    supportEmail: 'facturacion@example.com',
    phone: '4610000000',
);
```

Después:

```php
$organization = $billing->updateOrganizationLegalData(
    $organizationId,
    $legalData,
);
```

### OrganizationAddressData

Parámetros disponibles:

```text
new OrganizationAddressData(
    zip: string,
    street: ?string,
    exterior: ?string,
    interior: ?string,
    neighborhood: ?string,
    city: ?string,
    municipality: ?string,
    state: ?string,
);
```

`zip` es obligatorio.

Los demás valores son opcionales.

### OrganizationLegalData

Parámetros disponibles:

```text
new OrganizationLegalData(
    name: string,
    legalName: string,
    taxSystem: string,
    address: OrganizationAddressData,
    website: ?string,
    supportEmail: ?string,
    phone: ?string,
);
```

Valores obligatorios:

```text
name
legalName
taxSystem
address
```

`taxSystem` debe contener el código SAT correspondiente.

Ejemplo:

```text
taxSystem: '601'
```

No debe utilizarse el ID interno de una tabla de la aplicación.

---

## Consultar el estado de configuración

Después de crear o actualizar una Organization pueden consultarse:

```php
$organization->productionReady;
$organization->pendingSteps;
```

Ejemplo:

```php
if (!$organization->productionReady) {
    foreach ($organization->pendingSteps as $step) {
        logger()->info($step);
    }
}
```

`productionReady` indica si Facturapi considera que la Organization está lista
para operar en producción.

`pendingSteps` contiene los pasos pendientes reportados por Facturapi.

---

## Subir el CSD

Para cargar el Certificado de Sello Digital se utiliza:

```php
use SVR\Financial\Billing\DTO\OrganizationCertificateData;

$certificate = new OrganizationCertificateData(
    cerFile: storage_path('app/private/csd/certificate.cer'),
    keyFile: storage_path('app/private/csd/private.key'),
    password: $password,
);
```

Después:

```php
$organization = $billing->uploadOrganizationCertificate(
    $organizationId,
    $certificate,
);
```

### OrganizationCertificateData

Parámetros:

```text
new OrganizationCertificateData(
    cerFile: string,
    keyFile: string,
    password: string,
);
```

La librería valida que:

```text
cerFile exista
cerFile sea legible
keyFile exista
keyFile sea legible
password no esté vacío
```

Ejemplo completo:

```php
$certificate = new OrganizationCertificateData(
    cerFile: 'C:/certificados/empresa.cer',
    keyFile: 'C:/certificados/empresa.key',
    password: $password,
);

$organization = $billing->uploadOrganizationCertificate(
    $organizationId,
    $certificate,
);
```

La contraseña del CSD no debe enviarse al frontend ni almacenarse en logs.

---

## Usar una Organization en Sandbox

En ambiente de pruebas debe configurarse:

```dotenv
FACTURAPI_PRODUCTION=false
FACTURAPI_USER_KEY=
```

Para utilizar una Organization:

```php
use SVR\Financial\Billing\DTO\BillingContext;

$context = BillingContext::organization(
    $organizationId
);
```

En este modo SVR Financial obtiene automáticamente la Test API Key de la
Organization utilizando `FACTURAPI_USER_KEY`.

El contexto puede utilizarse para timbrar:

```php
$invoice = $billing->stamp(
    $invoiceData,
    $context,
);
```

También puede utilizarse para validar clientes fiscales:

```php
$validation = $billing->validateCustomer(
    $customerData,
    $context,
);
```

Y para crear clientes fiscales:

```php
$customer = $billing->createCustomer(
    $customerData,
    $context,
);
```

---

## Crear una Live API Key

Para utilizar una Organization en producción debe generarse una Live API Key.

```php
$liveApiKey = $billing->createOrganizationLiveApiKey(
    $organizationId
);
```

El resultado es un `string`:

```text
string $liveApiKey
```

Ejemplo:

```php
$liveApiKey = $billing->createOrganizationLiveApiKey(
    $organizationId
);
```

La aplicación debe almacenar esta llave de forma segura.

No debe generarse una nueva Live API Key para cada operación.

---

## Usar una Organization en Producción

En producción debe configurarse:

```dotenv
FACTURAPI_PRODUCTION=true
```

Después debe proporcionarse la Live API Key:

```php
use SVR\Financial\Billing\DTO\BillingContext;

$context = BillingContext::organization(
    $organizationId,
    $liveApiKey,
);
```

Timbrar:

```php
$invoice = $billing->stamp(
    $invoiceData,
    $context,
);
```

Si `FACTURAPI_PRODUCTION=true` y no se proporciona una Live API Key, SVR
Financial lanzará un error de configuración.

---

## Facturar con una Organization

Una vez creado el contexto:

```php
$context = BillingContext::organization(
    $organizationId,
    $liveApiKey,
);
```

puede utilizarse el mismo `InvoiceData` que se utilizaría con la cuenta
principal:

```php
$invoice = $billing->stamp(
    $invoiceData,
    $context,
);
```

Resultado:

```php
$invoice->provider;
$invoice->providerId;
$invoice->uuid;
$invoice->createdAt;
```

---

## Facturar a Público en General con una Organization

También puede utilizarse una Organization para emitir una factura global:

```php
$invoice = $billing->stampPublicGeneral(
    $publicGeneralInvoiceData,
    $context,
);
```

La construcción de `PublicGeneralInvoiceData` se documenta en:

```text
docs/public-general.md
```

---

## Descargar PDF

Para descargar el PDF de una factura emitida mediante una Organization debe
utilizarse el mismo contexto:

```php
$pdf = $billing->downloadPdf(
    $invoice->providerId,
    $context,
);
```

El resultado es el contenido binario del PDF:

```text
string $pdf
```

Ejemplo en Laravel:

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

## Descargar XML

```php
$xml = $billing->downloadXml(
    $invoice->providerId,
    $context,
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

## Flujo completo de alta

Ejemplo de configuración de una nueva Organization:

```php
use SVR\Financial\Billing\BillingManager;
use SVR\Financial\Billing\DTO\OrganizationAddressData;
use SVR\Financial\Billing\DTO\OrganizationCertificateData;
use SVR\Financial\Billing\DTO\OrganizationData;
use SVR\Financial\Billing\DTO\OrganizationLegalData;

$billing = app(BillingManager::class);

/*
|--------------------------------------------------------------------------
| 1. Crear Organization
|--------------------------------------------------------------------------
*/

$organization = $billing->createOrganization(
    new OrganizationData(
        name: 'Distribuidor Demo',
    )
);

$organizationId = $organization->providerId;

/*
|--------------------------------------------------------------------------
| 2. Configurar datos fiscales
|--------------------------------------------------------------------------
*/

$address = new OrganizationAddressData(
    zip: '38000',
    street: 'Hidalgo',
    exterior: '10',
    neighborhood: 'Centro',
    city: 'Celaya',
    municipality: 'Celaya',
    state: 'Guanajuato',
);

$legalData = new OrganizationLegalData(
    name: 'Distribuidor Demo',
    legalName: 'DISTRIBUIDOR DEMO SA DE CV',
    taxSystem: '601',
    address: $address,
);

$organization = $billing->updateOrganizationLegalData(
    $organizationId,
    $legalData,
);

/*
|--------------------------------------------------------------------------
| 3. Cargar CSD
|--------------------------------------------------------------------------
*/

$certificate = new OrganizationCertificateData(
    cerFile: storage_path('app/private/csd/certificate.cer'),
    keyFile: storage_path('app/private/csd/private.key'),
    password: $password,
);

$organization = $billing->uploadOrganizationCertificate(
    $organizationId,
    $certificate,
);

/*
|--------------------------------------------------------------------------
| 4. Crear Live API Key
|--------------------------------------------------------------------------
*/

$liveApiKey = $billing->createOrganizationLiveApiKey(
    $organizationId
);
```

Al finalizar deben conservarse como mínimo:

```php
$organizationId;
$liveApiKey;
```

`$organizationId` identifica la Organization en Facturapi.

`$liveApiKey` permite operar con esa Organization en producción.

---

## Ejemplo de uso posterior

Una vez almacenados los datos:

```php
use SVR\Financial\Billing\DTO\BillingContext;

$context = BillingContext::organization(
    $organizationId,
    $liveApiKey,
);

$invoice = $billing->stamp(
    $invoiceData,
    $context,
);
```

Para sandbox:

```php
$context = BillingContext::organization(
    $organizationId
);
```

---

## Manejo de errores

Las operaciones de Organizations pueden lanzar:

```text
SVR\Financial\Billing\Exceptions\BillingProviderException
```

Ejemplo:

```php
use SVR\Financial\Billing\Exceptions\BillingProviderException;

try {
    $organization = $billing->createOrganization(
        $data
    );
} catch (BillingProviderException $error) {
    report($error);

    return response()->json([
        'message' => $error->error->message,
    ], 422);
}
```

Información disponible:

```php
$error->error->message;
$error->error->providerMessage;
$error->error->code;
$error->error->httpCode;
$error->error->category;
$error->error->path;
$error->error->logId;
```

Consulta también:

```text
docs/errors.md
```

---

## Persistencia recomendada

SVR Financial no guarda información en la base de datos de la aplicación.

La aplicación debe decidir dónde almacenar:

```text
Organization ID
Live API Key
relación con el emisor
estado de configuración
datos fiscales propios
```

Ejemplo conceptual:

```text
distributors
    id
    facturapi_organization_id
    facturapi_live_key
```

La estructura exacta pertenece al proyecto consumidor.

