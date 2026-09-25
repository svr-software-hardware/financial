# Pagos con OpenPay

El punto de entrada público es `PaymentManager`:

```php
use SVR\Financial\Payments\PaymentManager;

$payments = app(PaymentManager::class);
```

La aplicación debe tokenizar la tarjeta con las herramientas de OpenPay y
entregar el token resultante a la librería. SVR Financial no almacena tarjetas
ni modifica tablas del proyecto consumidor.

## Datos del cliente

```php
use SVR\Financial\Payments\DTO\CustomerData;

$customer = new CustomerData(
    name: 'Juan',
    lastName: 'Pérez',
    email: 'juan@example.com',
    phone: '4610000000',
);
```

| Parámetro | Tipo | Predeterminado |
|---|---|---:|
| `name` | `string` | — |
| `lastName` | `string` | — |
| `email` | `string` | — |
| `phone` | `?string` | `null` |

El teléfono es opcional. OpenPay no recibe `phone_number` cuando `phone` es
`null` o una cadena vacía.

## Opciones de OpenPay

```php
use SVR\Financial\Payments\OpenPay\DTO\OpenPayChargeOptions;

$options = new OpenPayChargeOptions(
    deviceSessionId: $deviceSessionId,
    useCardPoints: false,
    customerIp: $request->ip(),
);
```

| Parámetro | Tipo | Predeterminado | Uso |
|---|---|---:|---|
| `deviceSessionId` | `string` | — | Identificador antifraude generado para el dispositivo. |
| `useCardPoints` | `bool` | `false` | Solicita usar puntos de la tarjeta. |
| `customerIp` | `?string` | `null` | IP pública del comprador. |

Si `customerIp` es `null`, la fábrica utiliza `OPENPAY_PUBLIC_IP`. La IP
resuelta es obligatoria y debe ser válida.

## Datos y ejecución del cargo

```php
use SVR\Financial\Payments\DTO\CardChargeData;

$chargeData = new CardChargeData(
    amount: 100.00,
    description: 'Servicio de correo electrónico',
    paymentMethodId: $tokenId,
    customer: $customer,
    use3DSecure: false,
    redirectUrl: null,
    providerOptions: $options,
);

$charge = $payments->charge($chargeData);
```

| Parámetro | Tipo | Predeterminado | Uso |
|---|---|---:|---|
| `amount` | `float` | — | Importe mayor que cero; se redondea a dos decimales. |
| `description` | `string` | — | Descripción no vacía. |
| `paymentMethodId` | `string` | — | Token o identificador del método de pago. |
| `customer` | `CustomerData` | — | Datos del cliente. |
| `use3DSecure` | `bool` | `false` | Activa 3D Secure. |
| `redirectUrl` | `?string` | `null` | URL válida y obligatoria cuando se activa 3DS. |
| `providerOptions` | `?ChargeProviderOptions` | `null` | Para OpenPay debe ser `OpenPayChargeOptions`. |

El resultado es `SVR\Financial\Payments\Models\Charge`:

```php
$charge->provider;
$charge->providerId;
$charge->status;
$charge->amount;
$charge->authorization;
$charge->operationDate;
$charge->card;
$charge->redirectUrl;
$charge->error;
```

Cuando existe información de tarjeta:

```php
$charge->card?->holderName;
$charge->card?->cardNumber;
$charge->card?->bankCode;
$charge->card?->bankName;
$charge->card?->type;
```

## 3D Secure

```php
$chargeData = new CardChargeData(
    amount: 500.00,
    description: 'Compra con autenticación 3DS',
    paymentMethodId: $tokenId,
    customer: $customer,
    use3DSecure: true,
    redirectUrl: route('payments.callback'),
    providerOptions: new OpenPayChargeOptions(
        deviceSessionId: $deviceSessionId,
        customerIp: $request->ip(),
    ),
);

$charge = $payments->charge($chargeData);

if ($charge->requiresAction()) {
    return redirect()->away($charge->redirectUrl);
}
```

`requiresAction()` sólo devuelve `true` cuando el cargo está pendiente y
OpenPay entregó una URL. La aplicación debe conservar `providerId`, manejar el
retorno y consultar el estado final.

## Consultar un cargo

```php
$charge = $payments->getCharge($providerChargeId);

if ($charge->successful()) {
    // Confirmar la operación en la aplicación.
}
```

El identificador no puede estar vacío. `getCharge()` no recibe opciones por
llamada, por lo que utiliza la IP de respaldo `OPENPAY_PUBLIC_IP`.

## Estados y helpers

| Caso | Valor |
|---|---|
| `ChargeStatus::Pending` | `pending` |
| `ChargeStatus::Completed` | `completed` |
| `ChargeStatus::Failed` | `failed` |
| `ChargeStatus::Cancelled` | `cancelled` |
| `ChargeStatus::Expired` | `expired` |
| `ChargeStatus::Unknown` | `unknown` |

```php
$charge->successful();
$charge->failed();
$charge->pending();
$charge->requiresAction();
$charge->hasError();
```

## Errores

Los datos inválidos proporcionados por la aplicación lanzan
`InvalidChargeDataException`. Los errores técnicos de OpenPay lanzan
`PaymentProviderException`. Un rechazo bancario devuelve un `Charge` con
estado `Failed` y un `ChargeError`.

```php
use SVR\Financial\Payments\Exceptions\InvalidChargeDataException;
use SVR\Financial\Payments\Exceptions\PaymentProviderException;

try {
    $charge = $payments->charge($chargeData);

    if ($charge->failed()) {
        return response()->json([
            'message' => $charge->error?->message,
        ], 422);
    }
} catch (InvalidChargeDataException $error) {
    return response()->json([
        'message' => $error->getMessage(),
    ], 422);
} catch (PaymentProviderException $error) {
    report($error);

    return response()->json([
        'message' => $error->error->message,
    ], 503);
}
```

Consulta [Manejo de errores](errors.md) para conocer los metadatos disponibles.

## Persistencia

La aplicación debe definir idempotencia, registrar intentos y resultados, y
decidir cuándo confirma su operación de negocio. Normalmente conviene conservar:

```php
$charge->providerId;
$charge->authorization;
$charge->amount;
$charge->status;
```
