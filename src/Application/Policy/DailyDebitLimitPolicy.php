<?php
declare(strict_types=1);
namespace BankAccount\Application\Policy;

use BankAccount\Domain\Entity\Operation;
use BankAccount\Domain\Enum\OperationType;
use BankAccount\Domain\Exception\DailyDebitLimitExceededException;
use BankAccount\Domain\Policy\DebitLimitPolicy;

final class DailyDebitLimitPolicy implements DebitLimitPolicy
{
    public function __construct(private readonly int $maxDailyDebits)
    {
    }

    /**
     * @param Operation[] $operations
     * @throws DailyDebitLimitExceededException
     */
    public function verifyDebitAllowed(array $operations): void
    {
        $todayDebitsCount = $this->calculateTodayDebitsCount($operations);

        if ($todayDebitsCount >= $this->maxDailyDebits) {
            throw DailyDebitLimitExceededException::create($this->maxDailyDebits);
        }
    }

    private function calculateTodayDebitsCount(array $operations): int
    {
        $today = (new \DateTimeImmutable())->format('Y-m-d');

        return count(array_filter(
            $operations,
            fn(Operation $operation) => $operation->getType() === OperationType::DEBIT &&
                $operation->getDate()->format('Y-m-d') === $today
        ));
    }
}
