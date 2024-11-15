<?php
declare(strict_types=1);
namespace BankAccount\Domain\Entity;

use BankAccount\Domain\Enum\OperationType;
use BankAccount\Domain\Exception\NonPositiveOperationAmountException;
use BankAccount\Domain\Value\Money;

final class Operation
{
    private function __construct(
        private readonly int $id,
        private readonly Money $amount,
        private readonly OperationType $type,
        private readonly \DateTimeImmutable $date
    ) {
        if ($this->amount->getAmount() <= 0) {
            throw NonPositiveOperationAmountException::create($this->amount->getAmount());
        }
    }

    public static function credit(int $id, Money $amount): self
    {
        return new self(
            $id,
            $amount,
            OperationType::CREDIT,
            new \DateTimeImmutable()
        );
    }

    public static function debit(int $id, Money $amount): self
    {
        return new self(
            $id,
            $amount,
            OperationType::DEBIT,
            new \DateTimeImmutable()
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getType(): OperationType
    {
        return $this->type;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function equals(self $other): bool
    {
        return $this->id === $other->id;
    }
}