# Configuración

SVR Financial utiliza:

```text
config/financial.php
```

Las credenciales deben almacenarse mediante variables de entorno.

Nunca deben escribirse API Keys directamente dentro del código fuente.

---

# OpenPay

Variables:

```dotenv
FINANCIAL_PAYMENT_PROVIDER=openpay

OPENPAY_MERCHANT_ID=
OPENPAY_PRIVATE_KEY=
OPENPAY_COUNTRY=MX
OPENPAY_PRODUCTION=false
OPENPAY_PUBLIC_IP=
```

## FINANCIAL_PAYMENT_PROVIDER

Proveedor de pagos utilizado.

Actualmente:

```text
openpay
```

## OPENPAY_MERCHANT_ID

Merchant ID proporcionado por OpenPay.

## OPENPAY_PRIVATE_KEY

Private Key de OpenPay utilizada por el backend.

Nunca debe enviarse al frontend.

## OPENPAY_COUNTRY

País de la cuenta.

Ejemplo:

```dotenv
OPENPAY_COUNTRY=MX
```

## OPENPAY_PRODUCTION

Sandbox:

```dotenv
OPENPAY_PRODUCTION=false
```

Producción:

```dotenv
OPENPAY_PRODUCTION=true
```

## OPENPAY_PUBLIC_IP

IP por defecto utilizada para operaciones donde OpenPay la requiera.

---

# Facturapi

Variables:

```dotenv
FINANCIAL_BILLING_PROVIDER=facturapi

FACTURAPI_KEY=
FACTURAPI_USER_KEY=
FACTURAPI_PRODUCTION=false
```

## FACTURAPI_KEY

API Key principal de Facturapi.

Se utiliza cuando la operación utiliza:

```php
BillingContext::default()
```

o cuando no se proporciona un `BillingContext`.

## FACTURAPI_USER_KEY

User Secret Key de Facturapi.

Se utiliza para operaciones relacionadas con Organizations.

En sandbox permite resolver automáticamente la Test API Key de una
Organization.

## FACTURAPI_PRODUCTION

Sandbox:

```dotenv
FACTURAPI_PRODUCTION=false
```

Producción:

```dotenv
FACTURAPI_PRODUCTION=true
```

---

# Organizations

## Sandbox

Solo se necesita el ID de la Organization:

```php
BillingContext::organization(
    $organizationId
);
```

SVR Financial obtiene la Test API Key utilizando `FACTURAPI_USER_KEY`.

## Producción

Debe proporcionarse la Live API Key de la Organization:

```php
BillingContext::organization(
    $organizationId,
    $organizationLiveApiKey,
);
```

La aplicación es responsable de almacenar la Live API Key de forma segura.

---

# SSL en Windows

Si PHP devuelve:

```text
cURL error 60:
SSL certificate problem:
unable to get local issuer certificate
```

no debe desactivarse la verificación SSL.

Debe configurarse correctamente el CA bundle utilizado por PHP.

Ejemplo en `php.ini`:

```ini
curl.cainfo = "C:\ruta\cacert.pem"
openssl.cafile = "C:\ruta\cacert.pem"
```

Para conocer el `php.ini` utilizado por CLI:

```bash
php --ini
```

Después de modificarlo, reinicia la terminal o la sesión de Tinker.