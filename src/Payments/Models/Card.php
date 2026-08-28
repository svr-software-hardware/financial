<?php

declare(strict_types=1);

namespace SVR\Financial\Payments\Models;

use SVR\Financial\Enums\CardType;

final readonly class Card
{
    public function __construct(
        public ?string $holderName,
        public ?string $cardNumber,
        public ?string $bankCode,
        public ?string $bankName,
        public CardType $type = CardType::Unknown,
    ) {
    }
}