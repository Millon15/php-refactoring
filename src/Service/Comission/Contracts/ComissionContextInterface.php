<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\Comission\Contracts;

use Millon\PhpRefactoring\Service\Contracts\BinLookUpInterface;
use Millon\PhpRefactoring\Service\Contracts\ExchangeRatesInterface;

/** @inner */
interface ComissionContextInterface extends BinLookUpInterface, ExchangeRatesInterface
{
}
