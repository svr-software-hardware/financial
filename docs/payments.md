# Pagos con OpenPay

El acceso público al módulo de pagos se realiza mediante:

```php
SVR\Financial\Payments\PaymentManager
```

Los proyectos no deberían utilizar directamente:

```php
OpenPayGateway
OpenPayClientFactory
OpenPayChargeMapper
```

---

# Crear un cliente

```php
use SVR\Financial\Payments\DTO\CustomerData;

$customer = new CustomerData(
    name: 'Juan',
    lastName: 'Pérez',
    email: 'juan@example.com',
    phone: '4610000000',
);
```

---

# Configurar OpenPay

Las opciones específicas de OpenPay se proporcionan mediante:

```php
SVR\Financial\Payments\OpenPay\DTO\OpenPayChargeOptions
```

Ejemplo:

```php
use SVR\Financial\Payments\OpenPay\DTO\OpenPayChargeOptions;

$options = new OpenPayChargeOptions(
    deviceSessionId: $deviceSessionId,
    useCardPoints: false,
);
```

También puede proporcionarse la IP:

```php
$options = new OpenPayChargeOptions(
    deviceSessionId: $deviceSessionId,
    useCardPoints: false,
    customerIp: $request->ip(),
);
```

---

# Crear un cargo

```php
use SVR\Financial\Payments\DTO\CardChargeData;

$chargeData = new CardChargeData(
    amount: 100.00,
    description: 'Compra de prueba',
    paymentMethodId: $tokenId,
    customer: $customer,
    providerOptions: $options,
);
```

Ejecutar:

```php
use SVR\Financial\Payments\PaymentManager;

$payments = app(PaymentManager::class);

$charge = $payments->charge(
    $chargeData
);
```

---

# Resultado

El resultado es:

```php
SVR\Financial\Payments\Models\Charge
```

Propiedades principales:

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

Helpers:

```php
$charge->successful();
$charge->failed();
$charge->pending();
$charge->requiresAction();
$charge->hasError();
```

---

# Estados

Los estados se representan mediante:

```php
SVR\Financial\Payments\Enums\ChargeStatus
```

Estados:

```text
Pending
Completed
Failed
Cancelled
Expired
Unknown
```

El proyecto no debe comparar directamente estados propios de OpenPay.

Incorrecto:

```php
if ($status === 'completed') {
}
```

Correcto:

```php
if ($charge->successful()) {
}
```

---

# 3D Secure

Para activar 3D Secure:

```php
$chargeData = new CardChargeData(
    amount: 100.00,
    description: 'Compra con 3DS',
    paymentMethodId: $tokenId,
    customer: $customer,
    use3DSecure: true,
    redirectUrl: route('payment.callback'),
    providerOptions: new OpenPayChargeOptions(
        deviceSessionId: $deviceSessionId,
    ),
);
```

Ejecutar:

```php
$charge = $payments->charge(
    $chargeData
);
```

Si requiere interacción:

```php
if ($charge->requiresAction()) {
    return redirect()->away(
        $charge->redirectUrl
    );
}
```

Después de completar el proceso de OpenPay:

```php
$charge = $payments->getCharge(
    $providerChargeId
);
```

Comprobar:

```php
if ($charge->successful()) {
    // Ejecutar lógica del proyecto.
}
```

---

# Persistencia

SVR Financial no modifica tablas del proyecto.

La aplicación decide qué hacer con:

```php
$charge->providerId;
$charge->authorization;
$charge->amount;
$charge->status;
$charge->card;
```

Por ejemplo:

```text
Charge
   ↓
Mapper del proyecto
   ↓
transactions
```

La tabla `transactions` pertenece al proyecto, no a SVR Financial.