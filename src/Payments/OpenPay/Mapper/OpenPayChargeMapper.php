<?php

declare(strict_types=1);

namespace SVR\Financial\Payments\OpenPay\Mapper;

use DateTimeImmutable;
use Exception;
use Openpay\Resources\OpenpayCharge;
use SVR\Financial\Enums\CardType;
use SVR\Financial\Payments\Enums\ChargeStatus;
use SVR\Financial\Payments\Enums\PaymentProvider;
use SVR\Financial\Payments\Models\Card;
use SVR\Financial\Payments\Models\Charge;
use SVR\Financial\Payments\Models\ChargeError;

final class OpenPayChargeMapper {
  public function map(OpenpayCharge $openPayCharge): Charge {
    $card = $openPayCharge->card;
    $paymentMethod = $openPayCharge->payment_method;

    return new Charge(
      provider: PaymentProvider::OpenPay,
      providerId: $this->stringOrNull($openPayCharge->id),
      status: $this->mapStatus($openPayCharge->status),
      amount: (float) ($openPayCharge->amount ?? 0),
      authorization: $this->stringOrNull(
        $openPayCharge->authorization
      ),
      operationDate: $this->mapDate(
        $openPayCharge->operation_date
      ),
      card: is_object($card)
      ? $this->mapCard($card)
      : null,
      redirectUrl: is_object($paymentMethod)
      ? $this->stringOrNull($paymentMethod->url ?? null)
      : null,
      error: $this->mapEmbeddedError($openPayCharge),
    );
  }

  private function mapStatus(mixed $status): ChargeStatus {
    return match (strtolower(trim((string) $status))) {
      'completed' => ChargeStatus::Completed,

      'in_progress',
      'charge_pending' => ChargeStatus::Pending,

      'failed' => ChargeStatus::Failed,

      'cancelled',
      'canceled' => ChargeStatus::Cancelled,

      'expired' => ChargeStatus::Expired,

      default => ChargeStatus::Unknown,
    };
  }

  private function mapCard(object $openPayCard): Card {
    return new Card(
      holderName: $this->stringOrNull(
        $openPayCard->holder_name ?? null
      ),
      cardNumber: $this->stringOrNull(
        $openPayCard->card_number ?? null
      ),
      bankCode: $this->stringOrNull(
        $openPayCard->bank_code ?? null
      ),
      bankName: $this->stringOrNull(
        $openPayCard->bank_name ?? null
      ),
      type: $this->mapCardType(
        $openPayCard->type ?? null
      ),
    );
  }

  private function mapCardType(mixed $type): CardType {
    return match (strtolower(trim((string) $type))) {
      'credit' => CardType::Credit,
      'debit' => CardType::Debit,
      default => CardType::Unknown,
    };
  }

  private function mapEmbeddedError(
    OpenpayCharge $openPayCharge
  ): ?ChargeError {
    $message = $this->stringOrNull(
      $openPayCharge->error_message
    );

    if ($message === null) {
      return null;
    }

    return new ChargeError(
      code: null,
      message: $message,
      providerMessage: $message,
    );
  }

  private function mapDate(mixed $value): ?DateTimeImmutable {
    $date = $this->stringOrNull($value);

    if ($date === null) {
      return null;
    }

    try {
      return new DateTimeImmutable($date);
    } catch (Exception) {
      return null;
    }
  }

  private function stringOrNull(mixed $value): ?string {
    if ($value === null) {
      return null;
    }

    $value = trim((string) $value);

    return $value === '' ? null : $value;
  }
}