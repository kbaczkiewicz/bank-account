<?php
declare(strict_types=1);
namespace BankAccount\Domain\Value;

use BankAccount\Domain\Exception\IncompatibleCurrencyException;
use BankAccount\Domain\Exception\NegativeFeePercentageException;
use BankAccount\Domain\Exception\NegativeMoneyAmountException;

final class Money
{
    private function __construct(
        private readonly int $amount,
        private readonly Currency $currency
    ) {
        if ($amount < 0) {
            throw NegativeMoneyAmountException::create($this->amount);
        }
    }

    public static function create(int $amount, Currency $currency): self
    {
        return new self($amount, $currency);
    }

    public function add(Money $other): self
    {
        if (!$this->currency->equals($other->currency)) {
            throw IncompatibleCurrencyException::create($this->currency, $other->currency);
        }

        return new self(
            $this->amount + $other->amount,
            $this->currency
        );
    }

    public function subtract(Money $other): self
    {
        if (!$this->currency->equals($other->currency)) {
            throw IncompatibleCurrencyException::create($this->currency, $other->currency);
        }

        $newAmount = $this->amount - $other->amount;

        if ($newAmount < 0) {
            throw NegativeMoneyAmountException::create($newAmount);
        }

        return new self($newAmount, $this->currency);
    }

    public function multiplyByPercent(float $percent): self
    {
        if ($percent < 0) {
            throw NegativeFeePercentageException::create($percent);
        }

        $multiplier = (1 + $percent);
        $newAmount = (int) round($this->amount * $multiplier);

        return new self($newAmount, $this->currency);
    }

    public function isGreaterThan(Money $other): bool
    {
        if (!$this->currency->equals($other->currency)) {
            throw IncompatibleCurrencyException::create($this->currency, $other->currency);
        }

        return $this->amount > $other->amount;
    }

    public function isLessThan(Money $other): bool
    {
        if (!$this->currency->equals($other->currency)) {
            throw IncompatibleCurrencyException::create($this->currency, $other->currency);
        }

        return $this->amount < $other->amount;
    }

    public function equals(Money $other): bool
    {
        if (!$this->currency->equals($other->currency)) {
            throw IncompatibleCurrencyException::create($this->currency, $other->currency);
        }

        return $this->amount === $other->amount;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }
}
