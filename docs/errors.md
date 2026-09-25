# Manejo de errores

SVR Financial normaliza los errores de OpenPay y Facturapi para que la
aplicación no dependa de las excepciones internas de sus SDK.

## Pagos rechazados

Un rechazo bancario esperado devuelve un `Charge` fallido; no lanza
`PaymentProviderException`:

```php
$charge = $payments->charge($chargeData);

if ($charge->failed()) {
    return response()->json([
        'message' => $charge->error?->message,
        'code' => $charge->error?->code,
    ], 422);
}
```

## `PaymentProviderException`

Errores técnicos, de autenticación, solicitud o conexión de OpenPay lanzan
`SVR\Financial\Payments\Exceptions\PaymentProviderException`.

```php
use SVR\Financial\Payments\Exceptions\PaymentProviderException;

try {
    $charge = $payments->charge($chargeData);
} catch (PaymentProviderException $error) {
    report($error);

    return response()->json([
        'message' => $error->error->message,
    ], 503);
}
```

La excepción expone `$error->provider` y un `ChargeError` en `$error->error`:

```php
$error->error->code;
$error->error->message;
$error->error->providerMessage;
$error->error->httpCode;
$error->error->providerRequestId;
$error->error->category;
```

Los datos de entrada inválidos lanzan `InvalidChargeDataException`, no
`PaymentProviderException`.

## `BillingProviderException`

Clientes fiscales, facturas, descargas y Organizations pueden lanzar
`SVR\Financial\Billing\Exceptions\BillingProviderException`.

```php
use SVR\Financial\Billing\Exceptions\BillingProviderException;

try {
    $invoice = $billing->stamp($invoiceData);
} catch (BillingProviderException $error) {
    report($error);

    return response()->json([
        'message' => $error->error->message,
        'category' => $error->error->category->value,
    ], 422);
}
```

La excepción expone `$error->provider` y un `BillingError` en `$error->error`.

## `BillingError`

```php
$billingError->message;
$billingError->providerMessage;
$billingError->code;
$billingError->httpCode;
$billingError->category;
$billingError->path;
$billingError->logId;
```

| Propiedad | Tipo | Uso |
|---|---|---|
| `message` | `string` | Mensaje público. En validaciones `400/422` conserva el detalle informado por Facturapi. |
| `providerMessage` | `?string` | Mensaje original para diagnóstico. |
| `code` | `?string` | Código del proveedor o de la excepción. |
| `httpCode` | `?int` | Estado HTTP del proveedor. |
| `category` | `BillingErrorCategory` | Clasificación normalizada. |
| `path` | `?string` | Campo o ruta del dato rechazado. |
| `logId` | `?string` | Identificador de log de Facturapi. |

Los detalles técnicos de conexión, autenticación y errores `5xx` permanecen en
`providerMessage`; no se exponen en `message`.

## `BillingErrorCategory`

| Caso | Valor | Significado |
|---|---|---|
| `Configuration` | `configuration` | Configuración ausente o inválida. |
| `Connection` | `connection` | DNS, SSL, timeout o conexión. |
| `Authentication` | `authentication` | Credenciales o permisos rechazados. |
| `Validation` | `validation` | Datos fiscales o payload inválido, normalmente `400/422`. |
| `Provider` | `provider` | Error `5xx` de Facturapi. |
| `Unknown` | `unknown` | Categoría no identificada. |

```php
use SVR\Financial\Billing\Enums\BillingErrorCategory;

switch ($error->error->category) {
    case BillingErrorCategory::Validation:
        return response()->json([
            'message' => $error->error->message,
            'path' => $error->error->path,
        ], 422);

    case BillingErrorCategory::Authentication:
    case BillingErrorCategory::Configuration:
        return response()->json([
            'message' => $error->error->message,
        ], 500);

    default:
        report($error);

        return response()->json([
            'message' => $error->error->message,
        ], 503);
}
```

## Validación fiscal

`BillingManager::validateCustomer()` devuelve `FiscalValidation` con
`valid: false` en lugar de propagar `BillingProviderException`:

```php
$validation = $billing->validateCustomer($customerData);

if (!$validation->valid) {
    logger()->warning('Validación fiscal rechazada', [
        'category' => $validation->error?->category->value,
        'code' => $validation->error?->code,
        'http_code' => $validation->error?->httpCode,
        'path' => $validation->error?->path,
        'log_id' => $validation->error?->logId,
    ]);
}
```

## Logging seguro

```php
logger()->error('Facturapi rechazó una operación', [
    'category' => $error->error->category->value,
    'code' => $error->error->code,
    'http_code' => $error->error->httpCode,
    'path' => $error->error->path,
    'log_id' => $error->error->logId,
    'provider_message' => $error->error->providerMessage,
]);
```

No registre API keys, llaves privadas, contraseñas de CSD, tokens completos,
datos completos de tarjeta ni información fiscal innecesaria. No devuelva
`providerMessage` directamente al frontend.
