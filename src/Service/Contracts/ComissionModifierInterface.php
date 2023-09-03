<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\Contracts;

use Millon\PhpRefactoring\Entity\Collection\CurrencyCollection;
use Millon\PhpRefactoring\Entity\Country;

interface ComissionModifierInterface
{
    /**
     * @var string $comission comission in EUR
     *
     * @return string string representation of the comission
     */
    public function modify(
        string $comission,
        Country $country,
        CurrencyCollection $currencyCollection
    ): string;
}
