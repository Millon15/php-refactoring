<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Millon\PhpRefactoring\Test\Unit\Service\Comission\ComissionModifier;

use Millon\PhpRefactoring\Entity\Comission;
use Millon\PhpRefactoring\Entity\Person;
use Millon\PhpRefactoring\Service\Comission\ComissionModifier\Round as UnitUnderTest;
use Millon\PhpRefactoring\Service\Comission\Contracts\ComissionContextInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RoundTest extends TestCase
{
    protected ComissionContextInterface|MockObject $comissionContextMock;

    protected function setUp(): void
    {
        $this->comissionContextMock = $this->createMock(ComissionContextInterface::class);
    }

    private function setupComissionContextMock(): void
    {
        $this->comissionContextMock->expects($this->never())
            ->method('lookup');

        $this->comissionContextMock->expects($this->never())
            ->method('latest');
    }

    /** @return iterable<array<string, string> */
    public static function success(): iterable
    {
        $person = new Person('123456', '1.1324234652387', 'EUR');
        $comission = new Comission($person, $person->amount, $person->currency);

        yield [
            '$comission' => $comission,
            '$expectedComission' => $comission->withNewSum('1.14'),
        ];


        $person = new Person('123456', '1321.33983', 'EUR');
        $comission = new Comission($person, $person->amount, $person->currency);

        yield [
            '$comission' => $comission,
            '$expectedComission' => $comission->withNewSum('1321.34'),
        ];
    }

    #[DataProvider('success')]
    public function testModify(
        Comission $comission,
        Comission $expectedComission,
    ): void {
        $this->setupComissionContextMock();

        $resultComission = (new UnitUnderTest())->modify($comission, $this->comissionContextMock);

        $this->assertNotSame($expectedComission, $comission);
        $this->assertNotSame($expectedComission, $resultComission);

        $this->assertEquals($expectedComission, $resultComission);
    }
}
