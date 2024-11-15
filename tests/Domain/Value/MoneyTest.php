<?php
declare(strict_types=1);
namespace BankAccount\Test\Domain\Value;

use BankAccount\Domain\Exception\IncompatibleCurrencyException;
use BankAccount\Domain\Exception\NegativeFeePercentageException;
use BankAccount\Domain\Exception\NegativeMoneyAmountException;
use BankAccount\Domain\Value\Currency;
use BankAccount\Domain\Value\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    private Currency $usd;
    private Currency $eur;

    protected function setUp(): void
    {
        $this->usd = Currency::fromString('USD');
        $this->eur = Currency::fromString('EUR');
    }

    public function testCreatesValidMoney(): void
    {
        $money = Money::create(10000, $this->usd);
        $this->assertEquals(10000, $money->getAmount());
        $this->assertTrue($this->usd->equals($money->getCurrency()));
    }

    public function testThrowsExceptionForNegativeAmount(): void
    {
        $this->expectException(NegativeMoneyAmountException::class);
        Money::create(-10000, $this->usd);
    }

    public function testAddsMoneyWithSameCurrency(): void
    {
        $money1 = Money::create(10000, $this->usd);
        $money2 = Money::create(5000, $this->usd);

        $result = $money1->add($money2);

        $this->assertEquals(15000, $result->getAmount());
    }

    public function testSubtractsMoneyWithSameCurrency(): void
    {
        $money1 = Money::create(10000, $this->usd);
        $money2 = Money::create(5000, $this->usd);

        $result = $money1->subtract($money2);

        $this->assertEquals(5000, $result->getAmount());
    }

    public function testMultipliesByPercent(): void
    {
        $money = Money::create(10000, $this->usd);
        $result = $money->multiplyByPercent(0.1);

        $this->assertEquals(11000, $result->getAmount());
    }

    public function testThrowsExceptionForNegativePercent()
    {
        $this->expectException(NegativeFeePercentageException::class);
        $money = Money::create(10000, $this->usd);
        $money->multiplyByPercent(-0.01);
    }

    public function testHandlesRoundingCorrectly(): void
    {
        $money = Money::create(10000, $this->usd);
        $result = $money->multiplyByPercent(0.001);

        $this->assertEquals(10010, $result->getAmount());
    }

    public function testComparesMoneyValues(): void
    {
        $money1 = Money::create(10000, $this->usd);
        $money2 = Money::create(5000, $this->usd);

        $this->assertTrue($money1->isGreaterThan($money2));
        $this->assertTrue($money2->isLessThan($money1));
        $this->assertFalse($money1->equals($money2));
    }

    public function testThrowsExceptionWhenComparingDifferentCurrencies(): void
    {
        $money1 = Money::create(10000, $this->usd);
        $money2 = Money::create(10000, $this->eur);

        $this->expectException(IncompatibleCurrencyException::class);
        $money1->equals($money2);
    }
}