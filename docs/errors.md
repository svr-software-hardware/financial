# Manejo de errores

SVR Financial normaliza errores de los proveedores para evitar que los proyectos
dependan directamente de OpenPay o Facturapi.

---

# Pagos rechazados

Un cargo rechazado puede regresar un `Charge` fallido.

```php
$charge = $payments->charge(
    $chargeData
);

if ($charge->failed()) {
    return response()->json([
        'message' => $charge->error?->message,
    ], 422);
}
```

---

# Errores técnicos de pagos

Pueden lanzar:

```php
SVR\Financial\Payments\Exceptions\PaymentProviderException
```

Ejemplo:

```php
try {
    $charge = $payments->charge(
        $chargeData
    );
} catch (PaymentProviderException $error) {
    report($error);

    return response()->json([
        'message' => $error->error->message,
    ], 500);
}
```

---

# Facturapi

Errores técnicos de facturación lanzan:

```php
SVR\Financial\Billing\Exceptions\BillingProviderException
```

Ejemplo:

```php
use SVR\Financial\Billing\Exceptions\BillingProviderException;

try {
    $invoice = $billing->stamp(
        $invoiceData
    );
} catch (BillingProviderException $error) {
    report($error);

    return response()->json([
        'message' => $error->error->message,
    ], 500);
}
```

---

# BillingError

Puede consultarse:

```php
$error->error->message;
$error->error->providerMessage;
$error->error->code;
$error->error->httpCode;
$error->error->category;
$error->error->path;
$error->error->logId;
```

Para errores de validación HTTP `400` y `422`, `message` conserva el detalle
específico informado por Facturapi. Los errores de conexión, autenticación y del
servidor utilizan mensajes públicos seguros; el detalle técnico permanece en
`providerMessage` para diagnóstico y no debe enviarse directamente al frontend.

---

# Categorías

```php
SVR\Financial\Billing\Enums\BillingErrorCategory
```

Valores:

```text
Configuration
Connection
Authentication
Validation
Provider
Unknown
```

Ejemplo:

```php
use SVR\Financial\Billing\Enums\BillingErrorCategory;

try {
    $invoice = $billing->stamp(
        $invoiceData
    );
} catch (BillingProviderException $error) {
    switch ($error->error->category) {
        case BillingErrorCategory::Validation:
            // Datos incorrectos.
            break;

        case BillingErrorCategory::Connection:
            // Red, SSL o conexión.
            break;

        case BillingErrorCategory::Authentication:
            // Credenciales.
            break;

        case BillingErrorCategory::Configuration:
            // Configuración interna.
            break;

        case BillingErrorCategory::Provider:
            // Error del proveedor.
            break;

        default:
            // Error desconocido.
            break;
    }
}
```

---

# Mensaje público y mensaje del proveedor

`message` puede mostrarse o transformarse para el usuario:

```php
$error->error->message
```

`providerMessage` debe utilizarse principalmente para diagnóstico:

```php
$error->error->providerMessage
```

No se recomienda devolver `providerMessage` directamente al frontend.

---

# Logging

Se recomienda registrar información como:

```php
report($error);
```

o:

```php
logger()->error(
    $error->error->providerMessage
);
```

Nunca registrar:

```text
API Keys
Private Keys
contraseñas
tokens completos
información sensible innecesaria
```
