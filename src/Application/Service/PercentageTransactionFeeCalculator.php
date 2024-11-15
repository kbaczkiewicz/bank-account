<?php
declare(strict_types=1);
namespace BankAccount\Application\Service;

use BankAccount\Domain\Exception\NegativeFeePercentageException;
use BankAccount\Domain\Service\TransactionFeeCalculator;
use BankAccount\Domain\Value\Money;

final class PercentageTransactionFeeCalculator implements TransactionFeeCalculator
{
    public function __construct(private readonly float $feePercentage)
    {
        if ($feePercentage < 0) {
            throw NegativeFeePercentageException::create($this->feePercentage);
        }
    }

    public function calculateFee(Money $amount): Money
    {
        return $amount->multiplyByPercent($this->feePercentage);
    }
}
