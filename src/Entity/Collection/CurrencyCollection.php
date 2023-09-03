<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Entity\Collection;

final class CurrencyCollection
{
    public function __construct(
        public readonly string $base,
        public readonly \DateTimeImmutable $timestamp,
        /** @var array<string, float> */
        public readonly array $rates,
    ) {
    }
}
