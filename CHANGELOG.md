# Historial de versiones

## v1.2.0 — 2026-11-16

- Obtiene la identidad fiscal de la plataforma desde la organización principal configurada en Facturapi para emitir la factura del beneficiario hacia la plataforma.
- Conserva OpenPay, 3D Secure, consulta de cargos, errores normalizados, validación fiscal, Organizations, CSD y descargas CFDI.
- Dependencia estable de Financial Workflow v1.0.0. La persistencia, los procesos y las APIs pertenecen a Workflow; los cálculos y efectos de negocio pertenecen a la aplicación consumidora.

Requisitos: PHP ^8.4 y componentes Laravel ^12.0. Mantiene OpenPay SDK 3.1.0 y Facturapi PHP ^4.7. No incluye ni ejecuta migraciones.

Para instalar: `composer require "svr/financial:^1.2"`. Conservar el repositorio Composer y las credenciales configuradas en el proyecto.

Las etiquetas v1.0.0 a v1.1.1 permanecen disponibles en el historial Git.
