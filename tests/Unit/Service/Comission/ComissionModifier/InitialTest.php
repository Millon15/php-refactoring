<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Millon\PhpRefactoring\Test\Unit\Service\Comission\ComissionModifier;

use Millon\PhpRefactoring\Entity\Collection\Rates;
use Millon\PhpRefactoring\Entity\Comission;
use Millon\PhpRefactoring\Entity\Person;
use Millon\PhpRefactoring\Service\Comission\ComissionModifier\Initial as UnitUnderTest;
use Millon\PhpRefactoring\Service\Comission\Contracts\ComissionContextInterface;
use Millon\PhpRefactoring\Service\Comission\Exception\CalculationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class InitialTest extends TestCase
{
    protected ComissionContextInterface|MockObject $comissionContextMock;

    protected function setUp(): void
    {
        $this->comissionContextMock = $this->createMock(ComissionContextInterface::class);
    }

    private function setupComissionContextMock(Comission $comission, Rates $rates): void
    {
        $this->comissionContextMock->expects($this->never())
            ->method('lookup');

        if (Comission::DEFAULT_CURRENCY === $comission->person->currency) {
            $this->comissionContextMock->expects($this->never())
                ->method('latest');

            return;
        }

        $this->comissionContextMock->expects($this->once())
            ->method('latest')
            ->with()
            ->willReturn($rates);
    }

    /** @return iterable<array<string, string> */
    public static function success(): iterable
    {
        // Person has EUR amount
        $amount = '100';
        $person = new Person('123456', $amount, Comission::DEFAULT_CURRENCY);
        $comission = new Comission($person);
        $rates = new Rates(Comission::DEFAULT_CURRENCY, []);

        yield [
            '$comission' => $comission,
            '$expectedComission' => $comission->withNewSum($amount),
            '$rates' => $rates,
        ];

        // Person has non-EUR amount
        $amount = '200';
        $factor = '3.8';
        $rate = (string) ($amount / $factor);

        $person = new Person('123457', $amount, 'SSK');
        $comission = new Comission($person);
        $rates = new Rates(Comission::DEFAULT_CURRENCY, ['SSK' => $rate]);

        yield [
            '$comission' => $comission,
            '$expectedComission' => $comission->withNewSum($factor),
            '$rates' => $rates,
        ];
    }

    #[DataProvider('success')]
    public function testModify(
        Comission $comission,
        Comission $expectedComission,
        Rates $rates,
    ): void {
        $this->setupComissionContextMock($comission, $rates);

        $resultComission = (new UnitUnderTest())->modify($comission, $this->comissionContextMock);

        $this->assertNotSame($expectedComission, $comission);
        $this->assertNotSame($expectedComission, $resultComission);

        $this->assertEquals($expectedComission, $resultComission);
    }

    /** @return iterable<array<string, string> */
    public static function failure(): iterable
    {
        $person = new Person('123456', '100', 'NEU');
        $comission = new Comission($person);
        $rates = new Rates(Comission::DEFAULT_CURRENCY, []);

        yield [
            '$comission' => $comission,
            '$rates' => $rates,
        ];
    }

    #[DataProvider('failure')]
    public function testModifyFailure(
        Comission $comission,
        Rates $rates,
    ): void {
        $this->setupComissionContextMock($comission, $rates);

        $this->expectException(CalculationException::class);
        (new UnitUnderTest())->modify($comission, $this->comissionContextMock);
    }
}
