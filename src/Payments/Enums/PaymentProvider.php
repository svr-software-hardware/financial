<?php

declare(strict_types=1);

namespace SVR\Financial\Payments\Enums;

enum PaymentProvider: string
{
    case OpenPay = 'openpay';

    /*
     * Se agregará cuando exista su implementación:
     *
     * case Stripe = 'stripe';
     */
}