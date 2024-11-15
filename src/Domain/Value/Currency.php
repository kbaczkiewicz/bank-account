<?php
declare(strict_types=1);
namespace BankAccount\Domain\Value;

use BankAccount\Domain\Exception\InvalidCurrencyCodeException;

final class Currency
{
    private function __construct(private readonly string $code)
    {
        if (strlen($code) !== 3) {
            throw InvalidCurrencyCodeException::create($code);
        }
    }

    public static function fromString(string $code): self
    {
        return new self(strtoupper($code));
    }

    public function equals(Currency $other): bool
    {
        return $this->code === $other->code;
    }

    public function toString(): string
    {
        return $this->code;
    }
}
