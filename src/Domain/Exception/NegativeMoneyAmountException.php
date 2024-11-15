<?php
declare(strict_types=1);
namespace BankAccount\Domain\Exception;

final class NegativeMoneyAmountException extends \LogicException
{
    public static function create(int $amount): self
    {
        return new self(sprintf(
            'Money amount cannot be negative, got: %f',
            $amount
        ));
    }
}
