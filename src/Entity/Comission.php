<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Entity;

final class Comission
{
    const DEFAULT_CURRENCY = 'EUR';

    public function __construct(
        public readonly Person $person,
        public readonly null|string $sum = null,
        public readonly string $currency = self::DEFAULT_CURRENCY,
    ) {}

    public function isInitialized(): bool
    {
        return null !== $this->sum;
    }

    public function withNewSum(string $amount): self
    {
        return new self(
            $this->person,
            $amount,
            $this->currency,
        );
    }
}
