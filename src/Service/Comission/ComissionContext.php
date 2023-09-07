<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\Comission;

use Millon\PhpRefactoring\Entity\Collection\Rates;
use Millon\PhpRefactoring\Entity\Comission;
use Millon\PhpRefactoring\Entity\Country;
use Millon\PhpRefactoring\Service\Comission\Contracts\ComissionContextInterface;
use Millon\PhpRefactoring\Service\Comission\Exception\CalculationException;
use Millon\PhpRefactoring\Service\Contracts\BinLookUpInterface;
use Millon\PhpRefactoring\Service\Contracts\ExchangeRatesInterface;

/** TODO implement REAL caching */
final class ComissionContext implements ComissionContextInterface
{
    /** @var array<string, Country> map bin => Country */
    private array $countries = [];
    private readonly Rates $rates;

    public function __construct(
        private readonly BinLookUpInterface $binLookUp,
        private readonly ExchangeRatesInterface $exchangeRates,
    ) {}

    public function lookup(string $bin): Country
    {
        return $this->countries[$bin] ??= $this->requestBinLookup($bin);
    }

    public function latest(): Rates
    {
        return $this->rates ??= $this->requestRates();
    }

    private function requestBinLookup(string $bin): Country
    {
        try {
            $country = $this->binLookUp->lookup($bin);
        } catch (\Throwable $e) {
            throw new CalculationException(
                message: 'Comission calculation failed: error lookup bin',
                previous: $e,
            );
        }

        return $country;
    }

    private function requestRates(): Rates
    {
        try {
            $rates = $this->exchangeRates->latest();
        } catch (\Throwable $e) {
            throw new CalculationException(
                message: 'Comission calculation failed: error fetching latest exchange rates',
                previous: $e,
            );
        }

        if (Comission::DEFAULT_CURRENCY !== $rates->base) {
            throw new CalculationException('Base currency is not EUR');
        }

        return $rates;
    }
}
