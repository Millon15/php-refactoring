<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\Contracts;

use Millon\PhpRefactoring\Entity\Comission;
use Millon\PhpRefactoring\Entity\Person;
use Millon\PhpRefactoring\Service\Comission\Exception\CalculationException;

interface ComissionCalculatorInterface
{
    /** @throws CalculationException */
    public function calculate(Person $person): Comission;
}
