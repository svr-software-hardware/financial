<?php

declare(strict_types=1);

namespace SVR\Financial\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SVR\Financial\Payments\DTO\CardChargeData;
use SVR\Financial\Payments\DTO\CustomerData;
use SVR\Financial\Payments\OpenPay\DTO\OpenPayChargeOptions;
use SVR\Financial\Payments\OpenPay\OpenPayGateway;

final class OpenPayGatewayTest extends TestCase
{
    public static function missingPhones(): iterable
    {
        yield 'null' => [null];
        yield 'empty string' => [''];
    }

    #[DataProvider('missingPhones')]
    public function testPhoneNumberIsOmittedWhenItIsNotProvided(?string $phone): void
    {
        $payload = $this->buildPayload($phone);

        self::assertArrayNotHasKey('phone_number', $payload['customer']);
    }

    public function testPhoneNumberIsIncludedWhenItIsProvided(): void
    {
        $payload = $this->buildPayload('4421112233');

        self::assertSame('4421112233', $payload['customer']['phone_number']);
    }

    private function buildPayload(?string $phone): array
    {
        $reflection = new ReflectionClass(OpenPayGateway::class);
        $gateway = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('buildPayload');

        return $method->invoke(
            $gateway,
            new CardChargeData(
                amount: 500.0,
                description: 'Test charge',
                paymentMethodId: 'token_test',
                customer: new CustomerData(
                    name: 'Test',
                    lastName: 'Customer',
                    email: 'test@example.com',
                    phone: $phone,
                ),
            ),
            new OpenPayChargeOptions(deviceSessionId: 'device_test'),
        );
    }
}
