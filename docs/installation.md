# Instalación

SVR Financial requiere PHP 8.4+, Laravel 12.x y Composer. El paquete se llama
`svr/financial` y el repositorio es privado.

## Instalar desde el repositorio VCS

Agregue el repositorio al `composer.json` de la aplicación:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/svr-software-hardware/financial.git"
        }
    ]
}
```

Instale una versión compatible:

```bash
composer require svr/financial:^1.0
```

Como el repositorio es privado, cada equipo de desarrollo, servidor y proceso
de CI/CD debe tener acceso a GitHub. No guarde tokens ni credenciales de GitHub
en el repositorio de la aplicación.

## Desarrollo local con un repositorio `path`

Si la aplicación y la librería están disponibles localmente:

```text
workspace/
├── financial/
└── application/
```

configure en `application/composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../financial",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "svr/financial": "dev-main"
    }
}
```

Después ejecute desde la aplicación:

```bash
composer update svr/financial -W
```

Con `symlink: true`, los cambios realizados en la librería quedan disponibles
sin copiar manualmente sus archivos. Si el sistema no puede crear enlaces,
Composer puede instalar una copia; en ese caso ejecute nuevamente el update al
cambiar la librería.

No mantenga simultáneamente entradas VCS y `path` para el mismo paquete salvo
que controle de forma explícita la prioridad de los repositorios.

## Package discovery

Composer registra automáticamente:

```text
SVR\Financial\FinancialServiceProvider
```

Normalmente no es necesario agregar el provider manualmente a Laravel.

## Publicar la configuración

```bash
php artisan vendor:publish --tag=financial-config
```

El comando crea:

```text
config/financial.php
```

Agregue las variables necesarias al `.env` y consulte
[Configuración](configuration.md).

Después limpie la caché:

```bash
php artisan optimize:clear
```

## Verificar con Tinker

```bash
php artisan tinker
```

Pagos:

```php
use SVR\Financial\Payments\PaymentManager;

$payments = app(PaymentManager::class);
$payments::class;
$payments->provider();
```

Facturación:

```php
use SVR\Financial\Billing\BillingManager;

$billing = app(BillingManager::class);
$billing::class;
$billing->provider();
```

Las llamadas a `provider()` no contactan al proveedor; permiten confirmar que
Laravel resolvió los bindings del paquete.

También puede comprobar la versión instalada:

```bash
composer show svr/financial
```

## Actualizar el paquete

Para actualizar dentro de la restricción declarada por la aplicación:

```bash
composer update svr/financial -W
php artisan optimize:clear
```

Revise y confirme en Git tanto `composer.json` como `composer.lock`. En
producción instale el lockfile aprobado:

```bash
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
```

## Siguiente paso

- [Configuración](configuration.md)
- [Pagos con OpenPay](payments.md)
- [Facturación con Facturapi](billing.md)
- [Organizations](organizations.md)
- [Público en General](public-general.md)
- [Manejo de errores](errors.md)
