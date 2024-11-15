<?php
declare(strict_types=1);
namespace BankAccount\Domain\Entity;

use BankAccount\Domain\Enum\OperationType;
use BankAccount\Domain\Exception\IncompatibleCurrencyException;
use BankAccount\Domain\Exception\InsufficientFundsException;
use BankAccount\Domain\Exception\InvalidOperationTypeException;
use BankAccount\Domain\Policy\DebitLimitPolicy;
use BankAccount\Domain\Service\TransactionFeeCalculator;
use BankAccount\Domain\Value\Currency;
use BankAccount\Domain\Value\Money;

final class BankAccount
{
    /** @var Operation[] */
    private array $operations = [];

    public function __construct(
        private readonly int $id,
        private readonly Currency $currency,
        private readonly TransactionFeeCalculator $feeCalculator,
        private readonly DebitLimitPolicy $debitLimitPolicy
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function credit(Operation $operation): void
    {
        if (!$this->currency->equals($operation->getAmount()->getCurrency())) {
            throw IncompatibleCurrencyException::create(
                $this->currency,
                $operation->getAmount()->getCurrency()
            );
        }

        if ($operation->getType() !== OperationType::CREDIT) {
            throw new InvalidOperationTypeException('CREDIT');
        }

        $this->operations[] = $operation;
    }

    public function debit(Operation $operation): void
    {
        if (!$this->currency->equals($operation->getAmount()->getCurrency())) {
            throw IncompatibleCurrencyException::create(
                $this->currency,
                $operation->getAmount()->getCurrency()
            );
        }

        if ($operation->getType() !== OperationType::DEBIT) {
            throw new InvalidOperationTypeException('DEBIT');
        }

        $this->debitLimitPolicy->verifyDebitAllowed($this->operations);

        $amountWithFee = $this->feeCalculator->calculateFee($operation->getAmount());
        $currentBalance = $this->calculateBalance();

        if ($amountWithFee->getAmount() > $currentBalance->getAmount()) {
            throw InsufficientFundsException::create($amountWithFee, $currentBalance);
        }

        $this->operations[] = Operation::debit(
            $operation->getId(),
            $amountWithFee
        );
    }

    public function getBalance(): Money
    {
        return $this->calculateBalance();
    }

    private function calculateBalance(): Money
    {
        $balance = Money::create(0, $this->currency);

        foreach ($this->operations as $operation) {
            $balance = match ($operation->getType()) {
                OperationType::CREDIT => $balance->add($operation->getAmount()),
                OperationType::DEBIT => $balance->subtract($operation->getAmount())
            };
        }

        return $balance;
    }
}
