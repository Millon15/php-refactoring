<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\Comission\ComissionModifier;

use Millon\PhpRefactoring\Entity\Comission;
use Millon\PhpRefactoring\Service\Comission\Contracts\ComissionContextInterface;
use Millon\PhpRefactoring\Service\Comission\Contracts\ComissionModifierInterface;
use Millon\PhpRefactoring\Service\Comission\Exception\CalculationException;

final class Initial implements ComissionModifierInterface
{
    public function modify(Comission $comission, ComissionContextInterface $context): Comission
    {
        $person = $comission->person;

        if ($person->currency === Comission::DEFAULT_CURRENCY) {
            return $comission->withNewAmount($person->amount);
        }

        $rate = $context->latest()->rates[$person->currency]
            ?? throw new CalculationException(sprintf('Rate for "%s" has not been found', $person->currency));
        $sum = $person->amount / $rate;

        return $comission->withNewAmount((string) $sum);
    }
}
