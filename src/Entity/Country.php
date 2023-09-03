<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Entity;

use Symfony\Component\Serializer\Annotation\SerializedPath;

final class Country
{
    public function __construct(
        #[SerializedPath('[country][alpha2]')]
        public readonly string $alpha2,
        #[SerializedPath('[country][currency]')]
        public readonly string $currency,
    ) {
    }
}
