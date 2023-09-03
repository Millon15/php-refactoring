<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service;

use Millon\PhpRefactoring\Entity\Collection\CurrencyCollection;
use Millon\PhpRefactoring\Entity\Country;
use Millon\PhpRefactoring\Service\Contracts\ComissionModifierInterface;

final class CountryComissionModifier implements ComissionModifierInterface
{
    private const EU_ALPHA2 = [
        'AT',
        'BE',
        'BG',
        'CY',
        'CZ',
        'DE',
        'DK',
        'EE',
        'ES',
        'FI',
        'FR',
        'GR',
        'HR',
        'HU',
        'IE',
        'IT',
        'LT',
        'LU',
        'LV',
        'MT',
        'NL',
        'PO',
        'PT',
        'RO',
        'SE',
        'SI',
        'SK',
    ];
    private const EU_COMISSION_MULTIPLIER = '0.01';
    private const NON_EU_COMISSION_MULTIPLIER = '0.02';

    private function isEu(Country $country): bool
    {
        return in_array($country->alpha2, self::EU_ALPHA2, true);
    }

    /** TODO bcmath this implementation */
    public function modify(
        string $comission,
        Country $country,
        CurrencyCollection $currencyCollection
    ): string {
        $comissionMultiplier = $this->isEu($country)
            ? self::EU_COMISSION_MULTIPLIER
            : self::NON_EU_COMISSION_MULTIPLIER;

        return (string) ($comission * $comissionMultiplier);
    }
}
