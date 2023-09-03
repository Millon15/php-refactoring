<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\Contracts;

use Millon\PhpRefactoring\Entity\Person;

interface ComissionCalculatorInterface
{
    /** @return string string representation of the comission */
    public function calculate(Person $person): string;
}
