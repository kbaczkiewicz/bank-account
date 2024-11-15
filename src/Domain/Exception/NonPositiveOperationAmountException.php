<?php
declare(strict_types=1);
namespace BankAccount\Domain\Exception;

class NonPositiveOperationAmountException extends \DomainException
{
    public static function create(int $amount): self
    {
        return new self(sprintf(
            'Operation amount must be positive, got: %f',
            $amount
        ));
    }
}
