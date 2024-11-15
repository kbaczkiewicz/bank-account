<?php
declare(strict_types=1);
namespace BankAccount\Test\Domain\Value;

use BankAccount\Domain\Exception\InvalidCurrencyCodeException;
use BankAccount\Domain\Value\Currency;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    public function testCreatesValidCurrency(): void
    {
        $currency = Currency::fromString('USD');
        $this->assertEquals('USD', $currency->toString());
    }

    public function testNormalizesLowerCaseToUpperCase(): void
    {
        $currency = Currency::fromString('usd');
        $this->assertEquals('USD', $currency->toString());
    }

    public function testThrowsExceptionForTooShortCode(): void
    {
        $this->expectException(InvalidCurrencyCodeException::class);
        $this->expectExceptionMessage('Invalid currency code: US. Currency code must be exactly 3 characters long');
        Currency::fromString('US');
    }

    public function testThrowsExceptionForTooLongCode(): void
    {
        $this->expectException(InvalidCurrencyCodeException::class);
        $this->expectExceptionMessage('Invalid currency code: USDD. Currency code must be exactly 3 characters long');
        Currency::fromString('USDD');
    }

    public function testEqualsReturnsTrueForSameCurrency(): void
    {
        $currency1 = Currency::fromString('USD');
        $currency2 = Currency::fromString('USD');

        $this->assertTrue($currency1->equals($currency2));
    }

    public function testEqualsReturnsFalseForDifferentCurrencies(): void
    {
        $currency1 = Currency::fromString('USD');
        $currency2 = Currency::fromString('EUR');

        $this->assertFalse($currency1->equals($currency2));
    }

    public function testEqualsIgnoresCaseWhenComparingCurrencies(): void
    {
        $currency1 = Currency::fromString('USD');
        $currency2 = Currency::fromString('usd');

        $this->assertTrue($currency1->equals($currency2));
    }

    /**
     * @dataProvider validCurrencyCodesProvider
     */
    public function testAcceptsValidCurrencyCodes(string $code): void
    {
        $currency = Currency::fromString($code);
        $this->assertEquals(strtoupper($code), $currency->toString());
    }

    /**
     * @return array<array<string>>
     */
    public static function validCurrencyCodesProvider(): array
    {
        return [
            ['USD'],
            ['EUR'],
            ['GBP'],
            ['JPY'],
            ['CHF'],
            ['PLN'],
            ['usd'],
            ['eUr'],
        ];
    }
}
