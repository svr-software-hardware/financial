<?php

declare(strict_types=1);

namespace SVR\Financial\Payments\OpenPay;

use Openpay\Data\Openpay;
use Openpay\Data\OpenpayApi;
use SVR\Financial\Payments\OpenPay\Exceptions\OpenPayConfigurationException;

final readonly class OpenPayClientFactory
{
    public function __construct(
        private ?string $merchantId,
        private ?string $privateKey,
        private string $country = 'MX',
        private bool $production = false,
        private ?string $defaultCustomerIp = null,
    ) {
    }

    public function create(?string $customerIp = null): OpenpayApi
    {
        $merchantId = trim((string) $this->merchantId);
        $privateKey = trim((string) $this->privateKey);
        $country = strtoupper(trim($this->country));
        $resolvedCustomerIp = trim(
            (string) ($customerIp ?? $this->defaultCustomerIp)
        );

        if ($merchantId === '') {
            throw new OpenPayConfigurationException(
                'El Merchant ID de OpenPay no está configurado.'
            );
        }

        if ($privateKey === '') {
            throw new OpenPayConfigurationException(
                'La llave privada de OpenPay no está configurada.'
            );
        }

        if (!in_array($country, ['MX', 'CO', 'PE'], true)) {
            throw new OpenPayConfigurationException(
                "El país de OpenPay [{$country}] no es válido."
            );
        }

        if ($resolvedCustomerIp === '') {
            throw new OpenPayConfigurationException(
                'La IP pública del cliente no fue proporcionada.'
            );
        }

        if (
            filter_var(
                $resolvedCustomerIp,
                FILTER_VALIDATE_IP
            ) === false
        ) {
            throw new OpenPayConfigurationException(
                "La IP pública [{$resolvedCustomerIp}] no es válida."
            );
        }

        Openpay::setProductionMode($this->production);

        return Openpay::getInstance(
            $merchantId,
            $privateKey,
            $country,
            $resolvedCustomerIp,
        );
    }
}