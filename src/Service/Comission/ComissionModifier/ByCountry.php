<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\Comission\ComissionModifier;

use Millon\PhpRefactoring\Entity\Comission;
use Millon\PhpRefactoring\Entity\Country;
use Millon\PhpRefactoring\Service\Comission\Contracts\ComissionContextInterface;
use Millon\PhpRefactoring\Service\Comission\Contracts\ComissionModifierInterface;

final class ByCountry implements ComissionModifierInterface
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

    public function modify(Comission $comission, ComissionContextInterface $context): Comission
    {
        $country = $context->lookup($comission->person->bin);

        $comissionMultiplier = $this->isEu($country)
            ? self::EU_COMISSION_MULTIPLIER
            : self::NON_EU_COMISSION_MULTIPLIER;
        $sum = $comission->sum * $comissionMultiplier;

        return $comission->withNewSum((string) $sum);
    }

    private function isEu(Country $country): bool
    {
        return in_array($country->alpha2, self::EU_ALPHA2, true);
    }
}
