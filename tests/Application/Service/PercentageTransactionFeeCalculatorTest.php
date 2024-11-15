<?php
declare(strict_types=1);
namespace BankAccount\Test\Application\Service;

use BankAccount\Application\Service\PercentageTransactionFeeCalculator;
use BankAccount\Domain\Exception\NegativeFeePercentageException;
use BankAccount\Domain\Value\Currency;
use BankAccount\Domain\Value\Money;
use PHPUnit\Framework\TestCase;

class PercentageTransactionFeeCalculatorTest extends TestCase
{
    private Currency $currency;

    protected function setUp(): void
    {
        $this->currency = Currency::fromString('USD');
    }

    public function testCalculatesFeeCorrectly(): void
    {
        $calculator = new PercentageTransactionFeeCalculator(0.005);
        $amount = Money::create(10000, $this->currency);

        $result = $calculator->calculateFee($amount);

        $this->assertEquals(10050, $result->getAmount());
    }

    public function testCalculatesZeroFee(): void
    {
        $calculator = new PercentageTransactionFeeCalculator(0.0);
        $amount = Money::create(10000, $this->currency);

        $result = $calculator->calculateFee($amount);

        $this->assertEquals(10000, $result->getAmount());
    }

    public function testRoundsFeeCorrectly(): void
    {
        $calculator = new PercentageTransactionFeeCalculator(0.001);
        $amount = Money::create(10000, $this->currency);

        $result = $calculator->calculateFee($amount);

        $this->assertEquals(10010, $result->getAmount());
    }

    public function testHandlesSmallAmounts(): void
    {
        $calculator = new PercentageTransactionFeeCalculator(0.005);
        $amount = Money::create(100, $this->currency);

        $result = $calculator->calculateFee($amount);

        $this->assertEquals(101, $result->getAmount());
    }

    public function testHandlesLargeAmounts(): void
    {
        $calculator = new PercentageTransactionFeeCalculator(0.005);
        $amount = Money::create(1000000, $this->currency);

        $result = $calculator->calculateFee($amount);

        $this->assertEquals(1005000, $result->getAmount());
    }

    public function testThrowsExceptionForNegativeFeePercentage(): void
    {
        $this->expectException(NegativeFeePercentageException::class);
        new PercentageTransactionFeeCalculator(-0.005);
    }

    /**
     * @dataProvider feeCalculationProvider
     */
    public function testVariousFeeCalculations(
        int $amount,
        float $feePercentage,
        int $expectedAmount
    ): void {
        $calculator = new PercentageTransactionFeeCalculator($feePercentage);
        $money = Money::create($amount, $this->currency);

        $result = $calculator->calculateFee($money);

        $this->assertEquals($expectedAmount, $result->getAmount());
    }

    /**
     * @return array<array{amount: int, feePercentage: float, expectedAmount: int}>
     */
    public static function feeCalculationProvider(): array
    {
        return [
            [
                'amount' => 10000,
                'feePercentage' => 0.005,
                'expectedAmount' => 10050,
            ],
            [
                'amount' => 10000,
                'feePercentage' => 0.01,
                'expectedAmount' => 10100,
            ],
            [
                'amount' => 10000,
                'feePercentage' => 0.1,
                'expectedAmount' => 11000,
            ],
            [
                'amount' => 99,
                'feePercentage' => 0.005,
                'expectedAmount' => 99,
            ],
            [
                'amount' => 1,
                'feePercentage' => 0.005,
                'expectedAmount' => 1,
            ],
            [
                'amount' => 1000000,
                'feePercentage' => 0.005,
                'expectedAmount' => 1005000,
            ],
            [
                'amount' => 100,
                'feePercentage' => 0.005,
                'expectedAmount' => 101,
            ],
        ];
    }
}