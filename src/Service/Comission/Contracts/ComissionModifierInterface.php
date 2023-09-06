<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\Comission\Contracts;

use Millon\PhpRefactoring\Entity\Comission;

/** @inner */
interface ComissionModifierInterface
{
    public function modify(Comission $comission, ComissionContextInterface $context): Comission;
}
