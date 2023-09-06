<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Entity;

final class Person
{
    public function __construct(
        public readonly string $bin,
        public readonly string $amount,
        public readonly string $currency,
    ) {}
}
