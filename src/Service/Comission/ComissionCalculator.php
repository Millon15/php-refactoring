<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\Comission;

use Millon\PhpRefactoring\Entity\Comission;
use Millon\PhpRefactoring\Entity\Person;
use Millon\PhpRefactoring\Service\Comission\Contracts\ComissionContextInterface;
use Millon\PhpRefactoring\Service\Comission\Contracts\ComissionModifierInterface;
use Millon\PhpRefactoring\Service\Contracts\ComissionCalculatorInterface;

final class ComissionCalculator implements ComissionCalculatorInterface
{
    /** @param ComissionModifierInterface[] $comissionModifiers */
    public function __construct(
        private readonly ComissionContextInterface $context,
        private readonly iterable $comissionModifiers = [],
    ) {}

    public function calculate(Person $person): Comission
    {
        $comission = new Comission($person);

        foreach ($this->comissionModifiers as $comissionModifier) {
            $comission = $comissionModifier->modify($comission, $this->context);
        }

        return $comission;
    }
}
