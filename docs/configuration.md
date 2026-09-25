# Configuración

SVR Financial lee `config/financial.php`. Publique el archivo y coloque las
credenciales en variables de entorno; no escriba secretos directamente en PHP.

```bash
php artisan vendor:publish --tag=financial-config
php artisan optimize:clear
```

## Variables disponibles

```dotenv
FINANCIAL_PAYMENT_PROVIDER=openpay
OPENPAY_MERCHANT_ID=
OPENPAY_PRIVATE_KEY=
OPENPAY_COUNTRY=MX
OPENPAY_PRODUCTION=false
OPENPAY_PUBLIC_IP=

FINANCIAL_INVOICE_PROVIDER=facturapi
FACTURAPI_KEY=
FACTURAPI_USER_KEY=
FACTURAPI_PRODUCTION=false
```

## OpenPay

### `FINANCIAL_PAYMENT_PROVIDER`

Proveedor de pagos predeterminado. El único valor soportado actualmente es:

```dotenv
FINANCIAL_PAYMENT_PROVIDER=openpay
```

Otro valor provoca un error al resolver `PaymentGateway`.

### `OPENPAY_MERCHANT_ID`

Merchant ID de OpenPay. Es obligatorio al crear o consultar cargos.

### `OPENPAY_PRIVATE_KEY`

Llave privada usada exclusivamente en el backend. Nunca debe enviarse al
frontend ni incluirse en logs.

### `OPENPAY_COUNTRY`

País de la cuenta. El código acepta `MX`, `CO` o `PE` y utiliza `MX` de forma
predeterminada.

```dotenv
OPENPAY_COUNTRY=MX
```

### `OPENPAY_PRODUCTION`

```dotenv
# Sandbox
OPENPAY_PRODUCTION=false

# Producción
OPENPAY_PRODUCTION=true
```

La bandera configura el modo global del SDK de OpenPay.

### `OPENPAY_PUBLIC_IP`

IP de respaldo para procesos sin una petición HTTP o llamadas que no reciben
IP por operación. Es obligatoria cuando `OpenPayChargeOptions::customerIp` es
`null` y siempre se usa en `PaymentManager::getCharge()`.

Durante un cobro web es preferible pasar la IP real del comprador:

```php
new OpenPayChargeOptions(
    deviceSessionId: $deviceSessionId,
    customerIp: $request->ip(),
);
```

## Facturapi

### `FINANCIAL_INVOICE_PROVIDER`

Proveedor configurado para facturación:

```dotenv
FINANCIAL_INVOICE_PROVIDER=facturapi
```

Facturapi es el único proveedor implementado actualmente.

### `FACTURAPI_KEY`

API Key utilizada por la cuenta principal, es decir, cuando se omite el
`BillingContext` o se usa `BillingContext::default()`.

Configure una Test API Key en sandbox y la credencial correspondiente de la
cuenta principal en producción.

### `FACTURAPI_USER_KEY`

User Secret Key usada para administrar Organizations:

- crear una Organization;
- actualizar sus datos fiscales;
- cargar el CSD;
- crear o renovar su Live API Key;
- obtener automáticamente una Test API Key de una Organization en sandbox.

### `FACTURAPI_PRODUCTION`

```dotenv
# Sandbox
FACTURAPI_PRODUCTION=false

# Producción
FACTURAPI_PRODUCTION=true
```

Esta bandera determina cómo se resuelve la credencial de una Organization.

## Organizations en sandbox

Con:

```dotenv
FACTURAPI_USER_KEY=sk_user_...
FACTURAPI_PRODUCTION=false
```

puede crearse el contexto sólo con el ID:

```php
use SVR\Financial\Billing\DTO\BillingContext;

$context = BillingContext::organization(
    $organizationId
);
```

La librería solicita a Facturapi la Test API Key mediante `FACTURAPI_USER_KEY`.

## Organizations en producción

Con `FACTURAPI_PRODUCTION=true`, la Live API Key debe entregarse explícitamente:

```php
$context = BillingContext::organization(
    $organizationId,
    $organizationLiveApiKey,
);
```

Si se omite, la librería lanza un error de configuración. Las Live API Keys no
pueden recuperarse completas después de generarse; la aplicación debe cifrarlas
y almacenarlas en el momento de su creación.

Consulta [Organizations](organizations.md) para el flujo completo.

## Cambio de ambiente

Al pasar de sandbox a producción:

1. cambie `OPENPAY_PRODUCTION` y `FACTURAPI_PRODUCTION` a `true`;
2. reemplace las credenciales de prueba por credenciales productivas;
3. entregue la Live API Key en cada `BillingContext::organization()`;
4. limpie la caché de configuración.

```bash
php artisan optimize:clear
```

No mezcle credenciales sandbox y productivas.

## SSL en Windows

Si PHP devuelve `cURL error 60` o `unable to get local issuer certificate`, no
desactive la verificación SSL. Configure el CA bundle usado por PHP:

```ini
curl.cainfo = "C:\ruta\cacert.pem"
openssl.cafile = "C:\ruta\cacert.pem"
```

Compruebe qué archivo `php.ini` usa la CLI:

```bash
php --ini
```

Después reinicie la terminal, PHP-FPM, Apache o la sesión de Tinker según el
entorno.
