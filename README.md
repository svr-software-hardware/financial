# SVR Financial

Librería interna de SVR para estandarizar integraciones de pagos y facturación
en aplicaciones PHP/Laravel.

Actualmente soporta:

### Pagos

- OpenPay
- Cobros con tarjeta
- 3D Secure
- Consulta de cargos
- Uso de puntos
- Normalización de estados y errores

### Facturación

- Facturapi
- Clientes fiscales
- Validación fiscal
- Timbrado CFDI
- Descarga PDF/XML
- Cuenta principal
- Organizations

---

## Requisitos

- PHP 8.4+
- Laravel 12+
- Composer

---

## Instalación

La librería se encuentra en:

https://github.com/svr-software-hardware/financial

Agrega el repositorio al `composer.json` del proyecto:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/svr-software-hardware/financial"
        }
    ]
}
```

Después instala la librería:

```bash
composer require svr/financial:^1.0
```

Laravel descubrirá automáticamente:

```text
SVR\Financial\FinancialServiceProvider
```

> Si el repositorio es privado, el equipo deberá tener acceso al repositorio
> mediante GitHub y Composer deberá contar con las credenciales correspondientes.

Consulta la guía completa:

[Instalación](docs/installation.md)

---

## Uso rápido

### OpenPay

```php
use SVR\Financial\Payments\PaymentManager;

$payments = app(PaymentManager::class);

$charge = $payments->charge($chargeData);
```

### Facturapi

```php
use SVR\Financial\Billing\BillingManager;

$billing = app(BillingManager::class);

$invoice = $billing->stamp($invoiceData);
```

### Facturapi Organization

```php
use SVR\Financial\Billing\DTO\BillingContext;

$invoice = $billing->stamp(
    $invoiceData,
    BillingContext::organization($organizationId),
);
```

---

## Documentación

- [Instalación](docs/installation.md)
- [Configuración](docs/configuration.md)
- [Pagos con OpenPay](docs/payments.md)
- [Facturación con Facturapi](docs/billing.md)
- [Manejo de errores](docs/errors.md)
- [Arquitectura](docs/architecture.md)

---

## Principio de diseño

La aplicación conoce el dominio.

SVR Financial conoce al proveedor financiero.

La librería no debe conocer conceptos propios de los proyectos, como:

```text
Sale
StandRequest
Ticket
Company
User
Transaction
```

La aplicación convierte sus datos en DTOs de SVR Financial y recibe modelos
normalizados como resultado.

---

## Proveedores actuales

| Área | Proveedor |
|---|---|
| Pagos | OpenPay |
| Facturación | Facturapi |

---

## Versión

Versión inicial:

```text
v1.0.0
```