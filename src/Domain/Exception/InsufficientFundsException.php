<?php
declare(strict_types=1);
namespace BankAccount\Domain\Exception;

use BankAccount\Domain\Value\Money;

final class InsufficientFundsException extends \DomainException
{
    public static function create(Money $attempted, Money $available): self
    {
        return new self(sprintf(
            'Cannot perform debit operation of %s %s: available balance is %s %s',
            $attempted->getAmount(),
            $attempted->getCurrency()->toString(),
            $available->getAmount(),
            $available->getCurrency()->toString()
        ));
    }
}
