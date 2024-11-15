<?php
declare(strict_types=1);
namespace BankAccount\Test\Domain\Entity;

use BankAccount\Domain\Entity\Operation;
use BankAccount\Domain\Enum\OperationType;
use BankAccount\Domain\Exception\NonPositiveOperationAmountException;
use BankAccount\Domain\Value\Currency;
use BankAccount\Domain\Value\Money;
use PHPUnit\Framework\TestCase;

class OperationTest extends TestCase
{
    private Currency $currency;
    private int $operationId;
    private Money $amount;

    protected function setUp(): void
    {
        $this->currency = Currency::fromString('USD');
        $this->operationId = 1;
        $this->amount = Money::create(1000, $this->currency);
    }

    public function testCreatesCreditOperation(): void
    {
        $operation = Operation::credit($this->operationId, $this->amount);

        $this->assertEquals($this->operationId, $operation->getId());
        $this->assertEquals($this->amount, $operation->getAmount());
        $this->assertEquals(OperationType::CREDIT, $operation->getType());
        $this->assertInstanceOf(\DateTimeImmutable::class, $operation->getDate());
    }

    public function testCreatesDebitOperation(): void
    {
        $operation = Operation::debit($this->operationId, $this->amount);

        $this->assertEquals($this->operationId, $operation->getId());
        $this->assertEquals($this->amount, $operation->getAmount());
        $this->assertEquals(OperationType::DEBIT, $operation->getType());
        $this->assertInstanceOf(\DateTimeImmutable::class, $operation->getDate());
    }

    public function testThrowsExceptionForZeroCreditAmount(): void
    {
        $this->expectException(NonPositiveOperationAmountException::class);

        Operation::credit(
            $this->operationId,
            Money::create(0, $this->currency)
        );
    }

    public function testThrowsExceptionForZeroDebitAmount(): void
    {
        $this->expectException(NonPositiveOperationAmountException::class);

        Operation::debit(
            $this->operationId,
            Money::create(0, $this->currency)
        );
    }

    public function testEqualsReturnsTrueForSameId(): void
    {
        $operation1 = Operation::credit($this->operationId, $this->amount);
        $operation2 = Operation::debit($this->operationId, Money::create(2000, $this->currency));

        $this->assertTrue($operation1->equals($operation2));
    }

    public function testEqualsReturnsFalseForDifferentIds(): void
    {
        $operation1 = Operation::credit($this->operationId, $this->amount);
        $operation2 = Operation::credit(2, $this->amount);

        $this->assertFalse($operation1->equals($operation2));
    }

    /**
     * @dataProvider validAmountsProvider
     */
    public function testAcceptsValidAmounts(int $amount): void
    {
        $money = Money::create($amount, $this->currency);

        $creditOperation = Operation::credit($this->operationId, $money);
        $debitOperation = Operation::debit($this->operationId, $money);

        $this->assertEquals($amount, $creditOperation->getAmount()->getAmount());
        $this->assertEquals($amount, $debitOperation->getAmount()->getAmount());
    }

    /**
     * @return array<array{amount: int}>
     */
    public static function validAmountsProvider(): array
    {
        return [
            ['amount' => 1],
            ['amount' => 100],
            ['amount' => 1000],
            ['amount' => PHP_INT_MAX],
        ];
    }
}