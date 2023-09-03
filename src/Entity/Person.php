<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Entity;

final class Person
{
    public function __construct(
        private readonly string $bin,
        private readonly string $amount,
        private readonly string $currency,
    ) {
    }
}
