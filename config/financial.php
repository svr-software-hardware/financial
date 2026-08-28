<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Payment provider
    |--------------------------------------------------------------------------
    */

    'payments' => [

        'default' => env('FINANCIAL_PAYMENT_PROVIDER', 'openpay'),

        'openpay' => [
            'merchant_id' => env('OPENPAY_MERCHANT_ID'),
            'private_key' => env('OPENPAY_PRIVATE_KEY'),
            'country' => env('OPENPAY_COUNTRY', 'MX'),
            'production' => env('OPENPAY_PRODUCTION', false),

            /*
             * Respaldo para procesos sin una petición HTTP, como comandos o jobs.
             * Durante un cobro web se debe enviar la IP real del comprador.
             */
            'public_ip' => env('OPENPAY_PUBLIC_IP'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice provider
    |--------------------------------------------------------------------------
    */

    'invoices' => [

        'default' => env('FINANCIAL_INVOICE_PROVIDER', 'facturapi'),

        'facturapi' => [
            'api_key' => env('FACTURAPI_KEY'),
            'user_key' => env('FACTURAPI_USER_KEY'),
        ],

    ],

];