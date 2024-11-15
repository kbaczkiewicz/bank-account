<?php
declare(strict_types=1);
namespace BankAccount\Domain\Exception;

use BankAccount\Domain\Value\Currency;

final class IncompatibleCurrencyException extends \DomainException
{
    public static function create(Currency $accountCurrency, Currency $operationCurrency): self
    {
        return new self(sprintf(
            'Cannot perform operation: account currency (%s) does not match operation currency (%s)',
            $accountCurrency->toString(),
            $operationCurrency->toString()
        ));
    }
}
