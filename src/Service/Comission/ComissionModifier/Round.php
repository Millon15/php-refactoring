<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\Comission\ComissionModifier;

use Millon\PhpRefactoring\Entity\Collection\Rates;
use Millon\PhpRefactoring\Entity\Comission;
use Millon\PhpRefactoring\Entity\Country;
use Millon\PhpRefactoring\Entity\Person;
use Millon\PhpRefactoring\Service\Comission\Contracts\ComissionContextInterface;
use Millon\PhpRefactoring\Service\Comission\Contracts\ComissionModifierInterface;

final class Round implements ComissionModifierInterface
{
    public function modify(Comission $comission, ComissionContextInterface $context): Comission
    {
        $sum = ceil($comission->sum * 100) / 100;

        return $comission->withNewSum((string) $sum);
    }
}
