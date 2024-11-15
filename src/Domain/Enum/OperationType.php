<?php
declare(strict_types=1);
namespace BankAccount\Domain\Enum;

enum OperationType
{
    case CREDIT;
    case DEBIT;
}
