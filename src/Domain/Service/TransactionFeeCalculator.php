<?php
declare(strict_types=1);
namespace BankAccount\Domain\Service;

use BankAccount\Domain\Value\Money;

interface TransactionFeeCalculator
{
    public function calculateFee(Money $amount): Money;
}
