<?php
declare(strict_types=1);
namespace BankAccount\Domain\Exception;

final class NegativeFeePercentageException extends \LogicException
{
    public static function create(float $percentage): self
    {
        return new self(sprintf(
            'Fee percentage cannot be negative, got: %f',
            $percentage
        ));
    }
}
