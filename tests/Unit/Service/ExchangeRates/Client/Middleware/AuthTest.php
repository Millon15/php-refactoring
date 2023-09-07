<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Test\Unit\Service\ExchangeRates\Client\Middleware;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Millon\PhpRefactoring\Service\ExchangeRates\Client\Middleware\Auth as UnitUnderTest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class AuthTest extends TestCase
{

    /** @return array<array<string, string> */
    public static function success(): array
    {
        $apiKey = 'api-key';

        return [
            [
                '$method' => 'GET',
                '$uri' => 'http://example.com',
                '$apiKey' => $apiKey,
                '$expectedQuery' => "access_key=$apiKey",
            ],
        ];
    }

    public static function handler(RequestInterface $request, array $options): callable
    {
        return function() use ($request, $options) { return [$request, $options]; };
    }

    #[DataProvider('success')]
    public function testAuth(string $method, string $uri, string $apiKey): void
    {
        $initialRequest = new Request($method, $uri);
        $authMiddleware = new UnitUnderTest($apiKey);

        [$request, $options] = $authMiddleware([$this, 'handler'])($initialRequest, [])();

        $this->assertEmpty($options);
        $this->assertNotEquals($initialRequest, $request);
        $this->assertEquals($initialRequest->getMethod(), $request->getMethod());
        $this->assertEquals($initialRequest->getUri()->getHost(), $request->getUri()->getHost());
        $this->assertStringEndsWith($apiKey, $request->getUri()->getQuery());
    }
}
