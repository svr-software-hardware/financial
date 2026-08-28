# Instalación

SVR Financial es una librería interna desarrollada para proyectos de SVR.

Repositorio:

https://github.com/svr-software-hardware/financial

---

## Requisitos

La versión 1 requiere:

```text
PHP 8.4+
Laravel 12+
Composer
```

---

## Agregar el repositorio

Como la librería no se distribuye mediante Packagist, el proyecto consumidor
debe indicar a Composer dónde encontrarla.

En el `composer.json` del proyecto agrega:

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

Por ejemplo:

```json
{
    "name": "svr/example-project",

    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/svr-software-hardware/financial"
        }
    ],

    "require": {
        "php": "^8.4",
        "laravel/framework": "^12.0"
    }
}
```

---

## Instalar

Ejecuta:

```bash
composer require svr/financial:^1.0
```

Composer instalará también las dependencias necesarias de los proveedores
soportados.

---

## Repositorio privado

Si el repositorio es privado, la máquina que ejecuta Composer debe tener acceso
a GitHub.

Esto aplica tanto para:

```text
Desarrollo local
Servidor de pruebas
Producción
CI/CD
```

Las credenciales de GitHub no deben guardarse dentro del repositorio del
proyecto.

---

## Laravel Package Discovery

SVR Financial registra automáticamente:

```text
SVR\Financial\FinancialServiceProvider
```

por medio de Composer.

Por lo tanto, normalmente no es necesario modificar manualmente los providers
de Laravel.

---

## Publicar configuración

Si el Service Provider ofrece publicación de configuración, ejecuta:

```bash
php artisan vendor:publish
```

Selecciona la configuración correspondiente a SVR Financial.

El archivo resultante será:

```text
config/financial.php
```

---

## Limpiar caché

Después de instalar o modificar configuración:

```bash
php artisan optimize:clear
```

En desarrollo también puede utilizarse:

```bash
php artisan config:clear
php artisan cache:clear
```

---

## Verificar instalación

Puede comprobarse desde Tinker:

```bash
php artisan tinker
```

Pagos:

```php
use SVR\Financial\Payments\PaymentManager;

$payments = app(PaymentManager::class);

$payments::class;
```

Debe devolver:

```text
SVR\Financial\Payments\PaymentManager
```

Facturación:

```php
use SVR\Financial\Billing\BillingManager;

$billing = app(BillingManager::class);

$billing::class;
```

Debe devolver:

```text
SVR\Financial\Billing\BillingManager
```

---

## Actualizar

Para actualizar dentro de una versión compatible:

```bash
composer update svr/financial
```

La versión utilizada por cada proyecto debe mantenerse explícita en
`composer.json`.

Ejemplo:

```json
"svr/financial": "^1.0"
```