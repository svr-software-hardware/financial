<?php

declare(strict_types=1);

namespace SVR\Financial\Payments\Enums;

enum ChargeStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case Unknown = 'unknown';
}