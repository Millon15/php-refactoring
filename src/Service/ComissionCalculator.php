<?php

declare(strict_types = 1);

namespace Millon\PhpRefactoring\Service;

use Millon\PhpRefactoring\Entity\Collection\CurrencyCollection;
use Millon\PhpRefactoring\Entity\Country;
use Millon\PhpRefactoring\Entity\Person;
use Millon\PhpRefactoring\Service\Contracts\BinLookUpInterface;
use Millon\PhpRefactoring\Service\Contracts\ComissionCalculatorInterface;
use Millon\PhpRefactoring\Service\Contracts\ComissionModifierInterface;
use Millon\PhpRefactoring\Service\Contracts\ExchangeRatesInterface;
use Millon\PhpRefactoring\Service\Exception\CalculationException;

final class ComissionCalculator implements ComissionCalculatorInterface
{
    public function __construct(
        private readonly BinLookUpInterface $binLookUp,
        private readonly ExchangeRatesInterface $exchangeRates,
        private readonly iterable $comissionModifiers = [],
    ) {
    }

    /** TODO bcmath this implementation */
    public function calculate(Person $person): string
    {
        try {
            $country = $this->binLookUp->lookup($person->bin);
            $currencyCollection = $this->exchangeRates->latest();
        } catch (\Throwable $e) {
            throw new CalculationException(
                message: 'Comission calculation failed: error retreiving data',
                previous: $e,
            );
        }

        $comission = $this->init($person, $currencyCollection);
        $comission = $this->modify($comission, $country, $currencyCollection);

        return (string) (ceil($comission * 100) / 100);
    }

    private function init(
        Person $person,
        CurrencyCollection $currencyCollection
    ): string {
        if ('EUR' !== $currencyCollection->base) {
            throw new CalculationException('Base currency is not EUR');
        }

        if ($person->currency === $currencyCollection->base) {
            $comission = $person->amount;
        } else {
            $rate = $currencyCollection->rates[$person->currency]
                ?? throw new CalculationException(sprintf('Rate for "%s" has not been found', $person->currency));

            $comission = (string) ($person->amount / $rate);
        }

        return $comission;
    }

    private function modify(
        mixed $comission,
        Country $country,
        CurrencyCollection $currencyCollection
    ): string {
        /** @var ComissionModifierInterface $comissionModifier */
        foreach ($this->comissionModifiers as $comissionModifier) {
            $comission = $comissionModifier->modify($comission, $country, $currencyCollection);
        }

        return $comission;
    }
}
