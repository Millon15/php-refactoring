<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\Contracts;

use Millon\PhpRefactoring\Entity\Person;
use Millon\PhpRefactoring\Service\Exception\CalculationException;

interface ComissionCalculatorInterface
{
    /**
     * @return string string representation of the comission
     * @throws CalculationException
     */
    public function calculate(Person $person): string;
}
