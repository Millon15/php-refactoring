<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\ExchangeRates\Client\Middleware;

use Psr\Http\Message\RequestInterface;

/** @internal */
final class Auth
{
    public function __construct(
        private readonly string $apiKey,
    ) {
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $uri = $request->getUri();
            $uri = $uri->withQuery($uri->getQuery() . "&access_key=$this->apiKey");
            $request = $request->withUri($uri);

            return $handler($request, $options);
        };
    }
}
