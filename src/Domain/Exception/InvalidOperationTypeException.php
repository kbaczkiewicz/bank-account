<?php
declare(strict_types=1);
namespace BankAccount\Domain\Exception;

class InvalidOperationTypeException extends \DomainException
{
    public static function create(string $expectedType): self
    {
        return new self(sprintf(
            'Invalid operation type. Expected: %s',
            $expectedType
        ));
    }
}