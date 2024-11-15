<?php
declare(strict_types=1);
namespace BankAccount\Test\Application\Policy;

use BankAccount\Application\Policy\DailyDebitLimitPolicy;
use BankAccount\Domain\Entity\Operation;
use BankAccount\Domain\Enum\OperationType;
use BankAccount\Domain\Exception\DailyDebitLimitExceededException;
use BankAccount\Domain\Value\Currency;
use BankAccount\Domain\Value\Money;
use PHPUnit\Framework\TestCase;

class DailyDebitLimitPolicyTest extends TestCase
{
    private Currency $currency;

    protected function setUp(): void
    {
        $this->currency = Currency::fromString('USD');
    }

    public function testAllowsOperationsWithinLimit(): void
    {
        $policy = new DailyDebitLimitPolicy(3);
        $operations = [
            Operation::debit(1, Money::create(1000, $this->currency)),
            Operation::debit(2, Money::create(1000, $this->currency))
        ];

        $policy->verifyDebitAllowed($operations);

        $this->assertTrue(true);
    }

    public function testThrowsExceptionWhenLimitExceeded(): void
    {
        $policy = new DailyDebitLimitPolicy(2);
        $operations = [
            Operation::debit(1, Money::create(1000, $this->currency)),
            Operation::debit(2, Money::create(1000, $this->currency))
        ];

        $this->expectException(DailyDebitLimitExceededException::class);

        $policy->verifyDebitAllowed($operations);
    }

    public function testIgnoresCreditOperations(): void
    {
        $policy = new DailyDebitLimitPolicy(2);
        $operations = [
            Operation::credit(1, Money::create(1000, $this->currency)),
            Operation::credit(2, Money::create(1000, $this->currency)),
            Operation::debit(3, Money::create(1000, $this->currency))
        ];

        $policy->verifyDebitAllowed($operations);

        $this->assertTrue(true);
    }

    public function testIgnoresOperationsFromPreviousDays(): void
    {
        $policy = new DailyDebitLimitPolicy(1);
        $operations = [
            $this->createOperationForDate(1, '2023-01-01'),
            $this->createOperationForDate(2, '2023-01-01'),
        ];

        $policy->verifyDebitAllowed($operations);

        $this->assertTrue(true);
    }

    public function testHandlesEmptyOperationsArray(): void
    {
        $policy = new DailyDebitLimitPolicy(1);

        $policy->verifyDebitAllowed([]);

        $this->assertTrue(true);
    }

    /**
     * @dataProvider validLimitsProvider
     */
    public function testAcceptsValidLimits(int $limit): void
    {
        $policy = new DailyDebitLimitPolicy($limit);
        $this->assertTrue(true);
    }

    /**
     * @return array<array{limit: int}>
     */
    public static function validLimitsProvider(): array
    {
        return [
            ['limit' => 1],
            ['limit' => 2],
            ['limit' => 10],
            ['limit' => PHP_INT_MAX],
        ];
    }

    private function createOperationForDate(int $id, string $date): Operation
    {
        $operation = Operation::debit($id, Money::create(1000, $this->currency));

        $reflectionClass = new \ReflectionClass($operation);
        $instance = $reflectionClass->newInstanceWithoutConstructor();
        $dateProperty = $reflectionClass->getProperty('date');
        $dateProperty->setValue($instance, new \DateTimeImmutable($date));
        $typeProperty = $reflectionClass->getProperty('type');
        $typeProperty->setValue($instance, OperationType::DEBIT);

        return $instance;
    }
}
