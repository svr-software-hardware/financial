<?php

declare(strict_types=1);

namespace SVR\Financial\Billing\Enums;

enum GlobalPeriodicity: string
{
    case Day = 'day';
    case Week = 'week';
    case Fortnight = 'fortnight';
    case Month = 'month';
    case TwoMonths = 'two_months';
}