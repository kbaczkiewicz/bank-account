<?php
declare(strict_types=1);
namespace BankAccount\Domain\Exception;

final class InvalidCurrencyCodeException extends \DomainException
{
    public static function create(string $code): self
    {
        return new self(sprintf(
            'Invalid currency code: %s. Currency code must be exactly 3 characters long',
            $code
        ));
    }
}
