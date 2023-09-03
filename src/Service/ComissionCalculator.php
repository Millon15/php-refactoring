<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service;

use Millon\PhpRefactoring\Entity\Person;
use Millon\PhpRefactoring\Service\Contracts\BinLookUpInterface;
use Millon\PhpRefactoring\Service\Contracts\ComissionCalculatorInterface;
use Millon\PhpRefactoring\Service\Contracts\ExchangeRatesInterface;

final class ComissionCalculator implements ComissionCalculatorInterface
{
    public function __construct(
        private readonly BinLookUpInterface $binLookUp,
        private readonly ExchangeRatesInterface $exchangeRates,
    ) {
    }

    public function calculate(Person $person): string
    {
        return '0';
    }
}
