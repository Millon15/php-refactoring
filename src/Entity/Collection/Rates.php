<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Entity\Collection;

final class Rates
{
    public function __construct(
        public readonly string $base,
        /** @var array<string, float> */
        public readonly array $rates,
    ) {}
}
