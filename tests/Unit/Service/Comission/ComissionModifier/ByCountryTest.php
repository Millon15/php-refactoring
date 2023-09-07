<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Millon\PhpRefactoring\Test\Unit\Service\Comission\ComissionModifier;

use Millon\PhpRefactoring\Entity\Comission;
use Millon\PhpRefactoring\Entity\Country;
use Millon\PhpRefactoring\Entity\Person;
use Millon\PhpRefactoring\Service\Comission\ComissionModifier\ByCountry as UnitUnderTest;
use Millon\PhpRefactoring\Service\Comission\Contracts\ComissionContextInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ByCountryTest extends TestCase
{
    protected ComissionContextInterface|MockObject $comissionContextMock;

    protected function setUp(): void
    {
        $this->comissionContextMock = $this->createMock(ComissionContextInterface::class);
    }

    /** @return iterable<array<string, string> */
    public static function success(): iterable
    {
        // EU
        $bin = '123456';
        $person = new Person($bin, '100', 'EUR');
        $comission = new Comission($person);
        $country = new Country('AT', $person->currency);

        yield [
            '$comission' => $comission->withNewSum('100'),
            '$expectedComission' => $comission->withNewSum('1'),
            '$bin' => $bin,
            '$country' => $country,
        ];

        // non-EU
        $bin = '23456';
        $person = new Person($bin, '100', 'EUR');
        $comission = new Comission($person);
        $country = new Country('UA', $person->currency);

        yield [
            '$comission' => $comission->withNewSum('100'),
            '$expectedComission' => $comission->withNewSum('2'),
            '$bin' => $bin,
            '$country' => $country,
        ];
    }

    #[DataProvider('success')]
    public function testModify(
        Comission $comission,
        Comission $expectedComission,
        string $bin,
        Country $country,
    ): void {
        $this->comissionContextMock->expects($this->once())
            ->method('lookup')
            ->with($bin)
            ->willReturn($country);

        $resultComission = (new UnitUnderTest())->modify($comission, $this->comissionContextMock);

        $this->assertNotSame($expectedComission, $comission);
        $this->assertNotSame($expectedComission, $resultComission);
        $this->assertEquals($expectedComission, $resultComission);
    }
}
