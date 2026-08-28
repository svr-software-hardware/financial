<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Enums;

enum BillingErrorCategory: string
{
    case Configuration = 'configuration';
    case Connection = 'connection';
    case Authentication = 'authentication';
    case Validation = 'validation';
    case Provider = 'provider';
    case Unknown = 'unknown';
}