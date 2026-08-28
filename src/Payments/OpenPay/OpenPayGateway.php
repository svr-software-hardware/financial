<?php

declare(strict_types=1);

namespace SVR\Financial\Payments\OpenPay;

use Openpay\Data\OpenpayApiError;
use Openpay\Data\OpenpayApiTransactionError;
use SVR\Financial\Payments\Contracts\PaymentGateway;
use SVR\Financial\Payments\DTO\CardChargeData;
use SVR\Financial\Payments\Enums\ChargeStatus;
use SVR\Financial\Payments\Enums\PaymentProvider;
use SVR\Financial\Payments\Exceptions\InvalidChargeDataException;
use SVR\Financial\Payments\Exceptions\PaymentProviderException;
use SVR\Financial\Payments\Models\Charge;
use SVR\Financial\Payments\OpenPay\DTO\OpenPayChargeOptions;
use SVR\Financial\Payments\OpenPay\Mapper\OpenPayChargeMapper;
use SVR\Financial\Payments\OpenPay\Mapper\OpenPayErrorMapper;
use Throwable;

final readonly class OpenPayGateway implements PaymentGateway
{
    public function __construct(
        private OpenPayClientFactory $clientFactory,
        private OpenPayChargeMapper $chargeMapper,
        private OpenPayErrorMapper $errorMapper,
    ) {
    }

    public function provider(): PaymentProvider
    {
        return PaymentProvider::OpenPay;
    }

    public function charge(CardChargeData $data): Charge
    {
        $options = $this->getOptions($data);

        $this->validateChargeData($data, $options);

        try {
            $openPay = $this->clientFactory->create(
                $options->customerIp
            );

            $openPayCharge = $openPay->charges->create(
                $this->buildPayload($data, $options)
            );

            return $this->chargeMapper->map($openPayCharge);
        } catch (OpenpayApiTransactionError $error) {
            /*
             * Un rechazo bancario es un resultado esperado del intento
             * de cobro, no una falla técnica de nuestra aplicación.
             */
            return new Charge(
                provider: PaymentProvider::OpenPay,
                providerId: null,
                status: ChargeStatus::Failed,
                amount: $data->amount,
                error: $this->errorMapper->map($error),
            );
        } catch (OpenpayApiError $error) {
            throw new PaymentProviderException(
                provider: PaymentProvider::OpenPay,
                error: $this->errorMapper->map($error),
                previous: $error,
            );
        } catch (Throwable $error) {
            throw new PaymentProviderException(
                provider: PaymentProvider::OpenPay,
                error: $this->errorMapper->map($error),
                previous: $error,
            );
        }
    }

    public function getCharge(string $providerId): Charge
    {
        $providerId = trim($providerId);

        if ($providerId === '') {
            throw new InvalidChargeDataException(
                'El identificador del cargo es obligatorio.'
            );
        }

        try {
            $openPay = $this->clientFactory->create();

            $openPayCharge = $openPay->charges->get($providerId);

            return $this->chargeMapper->map($openPayCharge);
        } catch (OpenpayApiError $error) {
            throw new PaymentProviderException(
                provider: PaymentProvider::OpenPay,
                error: $this->errorMapper->map($error),
                previous: $error,
            );
        } catch (Throwable $error) {
            throw new PaymentProviderException(
                provider: PaymentProvider::OpenPay,
                error: $this->errorMapper->map($error),
                previous: $error,
            );
        }
    }

    private function getOptions(
        CardChargeData $data
    ): OpenPayChargeOptions {
        if (!(
            $data->providerOptions instanceof OpenPayChargeOptions
        )) {
            throw new InvalidChargeDataException(
                'El cobro requiere opciones válidas de OpenPay.'
            );
        }

        return $data->providerOptions;
    }

    private function validateChargeData(
        CardChargeData $data,
        OpenPayChargeOptions $options,
    ): void {
        if ($data->amount <= 0) {
            throw new InvalidChargeDataException(
                'El importe del cobro debe ser mayor que cero.'
            );
        }

        if (trim($data->description) === '') {
            throw new InvalidChargeDataException(
                'La descripción del cobro es obligatoria.'
            );
        }

        if (trim($data->paymentMethodId) === '') {
            throw new InvalidChargeDataException(
                'El identificador del método de pago es obligatorio.'
            );
        }

        if (trim($options->deviceSessionId) === '') {
            throw new InvalidChargeDataException(
                'El identificador de sesión del dispositivo es obligatorio.'
            );
        }

        if (
            $data->use3DSecure
            && (
                $data->redirectUrl === null
                || filter_var(
                    $data->redirectUrl,
                    FILTER_VALIDATE_URL
                ) === false
            )
        ) {
            throw new InvalidChargeDataException(
                'El cobro con 3D Secure requiere una URL de retorno válida.'
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(
        CardChargeData $data,
        OpenPayChargeOptions $options,
    ): array {
        $payload = [
            'method' => 'card',
            'source_id' => $data->paymentMethodId,
            'amount' => round($data->amount, 2),
            'description' => $data->description,
            'device_session_id' => $options->deviceSessionId,
            'use_card_points' => $options->useCardPoints,
            'use_3d_secure' => $data->use3DSecure,
            'customer' => [
                'name' => $data->customer->name,
                'last_name' => $data->customer->lastName,
                'email' => $data->customer->email,
                'phone_number' => $data->customer->phone,
            ],
        ];

        if ($data->use3DSecure) {
            $payload['redirect_url'] = $data->redirectUrl;
        }

        return $payload;
    }
}