<?php

/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Millon\PhpRefactoring\Test\Unit\Service\Comission;

use Millon\PhpRefactoring\Entity\Comission;
use Millon\PhpRefactoring\Entity\Person;
use Millon\PhpRefactoring\Service\Comission\ComissionCalculator as UnitUnderTest;
use Millon\PhpRefactoring\Service\Comission\Contracts\ComissionContextInterface;
use Millon\PhpRefactoring\Service\Comission\Contracts\ComissionModifierInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ComissionCalculatorTest extends TestCase
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

    /** @return ComissionModifierInterface[] */
    private function generateComissionModifiersVector(
        Comission $initialComission,
        array $modifiedComissionSums
    ): iterable {
        $person = $initialComission->person;
        $lastModifiedComission = $initialComission;

        foreach ($modifiedComissionSums as $modifiedComissionSum) {
            $modifiedComission = new Comission($person, $modifiedComissionSum);

            $mock = $this->createMock(ComissionModifierInterface::class);
            $mock->expects($this->once())
                ->method('modify')
                ->with($lastModifiedComission, $this->comissionContextMock)
                ->willReturn($modifiedComission);

            $lastModifiedComission = $modifiedComission;

            yield $mock;
        }
    }

    /** @return iterable<array<string, string> */
    public static function success(): iterable
    {
        $modifiedComissionSums = [
            '1882.32',
            '18.6211124',
            '1882.63',
        ];

        $person = new Person('123456', '1882.32', 'EUR');
        $comission = new Comission($person);

        yield [
            '$person' => $person,
            '$initialComission' => $comission,
            '$modifiedComissionSums' => $modifiedComissionSums,
        ];

        $person = new Person('23456', '11882.32', 'SEK');
        $comission = new Comission($person);

        yield [
            '$person' => $person,
            '$initialComission' => $comission,
            '$modifiedComissionSums' => $modifiedComissionSums,
        ];
    }

    #[DataProvider('success')]
    public function testModify(
        Person $person,
        Comission $initialComission,
        array $modifiedComissionSums,
    ): void {
        $this->setupComissionContextMock();
        $comissionModifiers = $this->generateComissionModifiersVector($initialComission, $modifiedComissionSums);

        $comissionCalculator = new UnitUnderTest($this->comissionContextMock, $comissionModifiers);
        $resultComission = $comissionCalculator->calculate($person);

        $this->assertNotSame($initialComission, $resultComission);
        $this->assertSame($person, $initialComission->person);
        $this->assertSame($person, $resultComission->person);
    }
}
