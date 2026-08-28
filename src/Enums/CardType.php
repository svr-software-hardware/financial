<?php

declare(strict_types=1);

namespace SVR\Financial\Enums;

enum CardType: string
{
    case Credit = 'credit';
    case Debit = 'debit';
    case Unknown = 'unknown';
}