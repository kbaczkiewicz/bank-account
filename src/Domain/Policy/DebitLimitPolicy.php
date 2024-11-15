<?php
declare(strict_types=1);
namespace BankAccount\Domain\Policy;

use BankAccount\Domain\Entity\Operation;

interface DebitLimitPolicy
{
    /**
     * @param Operation[] $operations
     */
    public function verifyDebitAllowed(array $operations): void;
}