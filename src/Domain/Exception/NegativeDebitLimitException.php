<?php
declare(strict_types=1);
namespace BankAccount\Domain\Exception;

final class NegativeDebitLimitException extends \DomainException
{
    public static function create(int $limit): self
    {
        return new self(sprintf(
            'Invalid daily debit limit: %d. Limit must be greater than zero',
            $limit
        ));
    }
}
