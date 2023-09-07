<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Millon\PhpRefactoring\Test\Unit\Service\Comission;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Millon\PhpRefactoring\Entity\Collection\Rates;
use Millon\PhpRefactoring\Entity\Comission;
use Millon\PhpRefactoring\Entity\Country;
use Millon\PhpRefactoring\Service\Comission\ComissionContext as UnitUnderTest;
use Millon\PhpRefactoring\Service\Comission\Exception\CalculationException;
use Millon\PhpRefactoring\Service\Contracts\BinLookUpInterface;
use Millon\PhpRefactoring\Service\Contracts\ExchangeRatesInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class ComissionContextTest extends TestCase
{
    private const MAX_CONSECUTIVE_CALLS = 3;

    protected BinLookUpInterface|MockObject $binLookUpMock;
    protected ExchangeRatesInterface|MockObject $exchangeRatesMock;

    protected function setUp(): void
    {
        $this->binLookUpMock = $this->createMock(BinLookUpInterface::class);
        $this->exchangeRatesMock = $this->createMock(ExchangeRatesInterface::class);
    }

    /** @return iterable<array<string, string> */
    public static function loockup(): iterable
    {
        yield ['123456', new Country('GB', 'GBP')];
        yield ['23456', new Country('FR', 'EUR')];
        yield ['3456', new Country('DE', 'EUR')];
        yield ['3456', new Country('PL', 'PLN')];
    }

    #[DataProvider('loockup')]
    public function testLoockup(string $bin, Country $country): void
    {
        $comissionContext = new UnitUnderTest($this->binLookUpMock, $this->exchangeRatesMock);

        $this->exchangeRatesMock->expects($this->never())->method('latest');
        $this->binLookUpMock->expects($this->once())
            ->method('lookup')
            ->with($bin)
            ->willReturn($country);

        for ($i = 0; $i < self::MAX_CONSECUTIVE_CALLS; $i++) {
            $resultCountry = $comissionContext->lookup($bin);

            $this->assertSame($resultCountry, $country);
        }
    }


    /** @return iterable<array<string, string> */
    public static function latest(): iterable
    {
        yield [new Rates(Comission::DEFAULT_CURRENCY, [])];
        yield [new Rates(Comission::DEFAULT_CURRENCY, ['SEK' => '10.1241'])];
    }

    #[DataProvider('latest')]
    public function testLatest(Rates $rates): void
    {
        $comissionContext = new UnitUnderTest($this->binLookUpMock, $this->exchangeRatesMock);

        $this->binLookUpMock->expects($this->never())->method('lookup');
        $this->exchangeRatesMock->expects($this->once())
            ->method('latest')
            ->willReturn($rates);

        for ($i = 0; $i < self::MAX_CONSECUTIVE_CALLS; $i++) {
            $resultRates = $comissionContext->latest();

            $this->assertSame($rates, $resultRates);
        }
    }

    public function testLookupFail(): void
    {
        $comissionContext = new UnitUnderTest($this->binLookUpMock, $this->exchangeRatesMock);

        $bin = '123456';
        $exception = new ConnectException(
            'test fail lookup',
            $this->createMock(RequestInterface::class),
        );

        $this->exchangeRatesMock->expects($this->never())->method('latest');
        $this->binLookUpMock->expects($this->once())
            ->method('lookup')
            ->with($bin)
            ->willThrowException($exception);

        for ($i = 0; $i < self::MAX_CONSECUTIVE_CALLS; $i++) {
            $this->expectException(CalculationException::class);
            $comissionContext->lookup($bin);
        }
    }

    public function testLatestFail(): void
    {
        $comissionContext = new UnitUnderTest($this->binLookUpMock, $this->exchangeRatesMock);

        $exception = new BadResponseException(
            'test fail latest',
            $this->createMock(RequestInterface::class),
            $this->createMock(ResponseInterface::class),
        );

        $this->binLookUpMock->expects($this->never())->method('lookup');
        $this->exchangeRatesMock->expects($this->once())
            ->method('latest')
            ->willThrowException($exception);

        for ($i = 0; $i < self::MAX_CONSECUTIVE_CALLS; $i++) {
            $this->expectException(CalculationException::class);
            $comissionContext->latest();
        }
    }
}
