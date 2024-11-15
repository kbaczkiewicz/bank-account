<?php
declare(strict_types=1);
namespace BankAccount\Test\Domain\Entity;

use BankAccount\Application\Policy\DailyDebitLimitPolicy;
use BankAccount\Application\Service\PercentageTransactionFeeCalculator;
use BankAccount\Domain\Entity\BankAccount;
use BankAccount\Domain\Entity\Operation;
use BankAccount\Domain\Exception\DailyDebitLimitExceededException;
use BankAccount\Domain\Exception\IncompatibleCurrencyException;
use BankAccount\Domain\Exception\InsufficientFundsException;
use BankAccount\Domain\Exception\InvalidOperationTypeException;
use BankAccount\Domain\Policy\DebitLimitPolicy;
use BankAccount\Domain\Value\Currency;
use BankAccount\Domain\Value\Money;
use PHPUnit\Framework\TestCase;

class BankAccountTest extends TestCase
{
    private Currency $usd;
    private Currency $eur;
    private int $accountId;
    private int $nextOperationId;
    private BankAccount $account;
    private DebitLimitPolicy $debitLimitPolicy;

    protected function setUp(): void
    {
        $this->usd = Currency::fromString('USD');
        $this->eur = Currency::fromString('EUR');
        $this->accountId = 1;
        $this->nextOperationId = 1;
        $this->debitLimitPolicy = new DailyDebitLimitPolicy(3);

        $this->account = new BankAccount(
            $this->accountId,
            $this->usd,
            new PercentageTransactionFeeCalculator(0.005),
            $this->debitLimitPolicy
        );
    }

    private function getNextOperationId(): int
    {
        $this->nextOperationId += 1;

        return $this->nextOperationId;
    }

    public function testNewAccountHasZeroBalance(): void
    {
        $this->assertEquals(0, $this->account->getBalance()->getAmount());
    }

    public function testCreditIncreasesBalance(): void
    {
        $operation = Operation::credit(
            $this->getNextOperationId(),
            Money::create(10000, $this->usd)
        );

        $this->account->credit($operation);

        $this->assertEquals(10000, $this->account->getBalance()->getAmount());
    }

    public function testDebitDecreasesBalanceWithFee(): void
    {
        $this->account->credit(Operation::credit(
            $this->getNextOperationId(),
            Money::create(10000, $this->usd)
        ));

        $this->account->debit(Operation::debit(
            $this->getNextOperationId(),
            Money::create(5000, $this->usd)
        ));

        $this->assertEquals(4975, $this->account->getBalance()->getAmount());
    }

    public function testCannotCreditWithDifferentCurrency(): void
    {
        $operation = Operation::credit(
            $this->getNextOperationId(),
            Money::create(10000, $this->eur)
        );

        $this->expectException(IncompatibleCurrencyException::class);
        $this->account->credit($operation);
    }

    public function testCannotDebitWithDifferentCurrency(): void
    {
        $operation = Operation::debit(
            $this->getNextOperationId(),
            Money::create(10000, $this->eur)
        );

        $this->expectException(IncompatibleCurrencyException::class);
        $this->account->debit($operation);
    }

    public function testCannotDebitWithInsufficientFunds(): void
    {
        $this->account->credit(Operation::credit(
            $this->getNextOperationId(),
            Money::create(10000, $this->usd)
        ));

        $this->expectException(InsufficientFundsException::class);
        $this->account->debit(Operation::debit(
            $this->getNextOperationId(),
            Money::create(9999, $this->usd)
        ));
    }

    public function testCannotExceedDailyDebitLimit(): void
    {
        $this->account->credit(Operation::credit(
            $this->getNextOperationId(),
            Money::create(100000, $this->usd)
        ));

        for ($i = 0; $i < 3; $i++) {
            $this->account->debit(Operation::debit(
                $this->getNextOperationId(),
                Money::create(1000, $this->usd)
            ));
        }

        $this->expectException(DailyDebitLimitExceededException::class);
        $this->account->debit(Operation::debit(
            $this->getNextOperationId(),
            Money::create(1000, $this->usd)
        ));
    }

    public function testCannotCreditWithDebitOperation(): void
    {
        $operation = Operation::debit(
            $this->getNextOperationId(),
            Money::create(1000, $this->usd)
        );

        $this->expectException(InvalidOperationTypeException::class);
        $this->account->credit($operation);
    }

    public function testCannotDebitWithCreditOperation(): void
    {
        $operation = Operation::credit(
            $this->getNextOperationId(),
            Money::create(1000, $this->usd)
        );

        $this->expectException(InvalidOperationTypeException::class);
        $this->account->debit($operation);
    }

    public function testMultipleOperationsCalculateCorrectBalance(): void
    {
        $this->account->credit(Operation::credit(
            $this->getNextOperationId(),
            Money::create(10000, $this->usd)
        ));

        $this->account->debit(Operation::debit(
            $this->getNextOperationId(),
            Money::create(2000, $this->usd)
        ));

        $this->account->credit(Operation::credit(
            $this->getNextOperationId(),
            Money::create(5000, $this->usd)
        ));

        $this->account->debit(Operation::debit(
            $this->getNextOperationId(),
            Money::create(1000, $this->usd)
        ));

        $this->assertEquals(11985, $this->account->getBalance()->getAmount());
    }
}
