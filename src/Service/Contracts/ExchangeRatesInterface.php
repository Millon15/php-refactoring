<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\Contracts;

use Millon\PhpRefactoring\Entity\Collection\Rates;

interface ExchangeRatesInterface
{
    public function latest(): Rates;
}
