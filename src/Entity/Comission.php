<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Entity;

final class Comission
{
    const DEFAULT_CURRENCY = 'EUR';

    public function __construct(
        public readonly Person $person,
        public readonly null|string $totalAmount = null,
        public readonly string $currency = self::DEFAULT_CURRENCY,
    ) {}

    public function isInitialized(): bool
    {
        return null !== $this->totalAmount;
    }

    public function comissionSum(): string
    {
        $sum = $this->person->amount - $this->totalAmount;

        return (string) $sum;
    }

    public function withNewAmount(string $amount): self
    {
        return new self(
            $this->person,
            $amount,
            $this->currency,
        );
    }
}
