<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\Contracts;

use Millon\PhpRefactoring\Entity\Country;

interface BinLookUpInterface
{
    public function lookup(string $bin): Country;
}
