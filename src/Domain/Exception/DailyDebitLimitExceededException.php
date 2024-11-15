<?php
declare(strict_types=1);
namespace BankAccount\Domain\Exception;

final class DailyDebitLimitExceededException extends \DomainException
{
    public static function create(int $maxDailyDebits): self
    {
        return new self(sprintf(
            'Cannot perform debit operation: daily limit of %d operations has been exceeded',
            $maxDailyDebits
        ));
    }
}
