<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Millon\PhpRefactoring\Test\Integration\Service\Binlist\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;
use Millon\PhpRefactoring\Service\Binlist\Client\Client as UnitUnderTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;

final class ClientTest extends KernelTestCase
{
    protected HttpClient|MockObject $mockClient;
    protected SerializerInterface|MockObject $serializerMock;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->mockClient = $this->createMock(HttpClient::class);
    }

    /** @return array<array<string, string> */
    public static function success(): array
    {
        return [
            [
                '$baseUrl' => 'https://api.example.com',
                '$bin' => '123456',
                '$alpha2' => 'DK',
                '$currency' => 'DKK',
                '$body' => '{"number":{"length":16,"luhn":true},"scheme":"visa","type":"debit","brand":"Visa/Dankort","prepaid":false,"country":{"numeric":"208","alpha2":"DK","name":"Denmark","emoji":"🇩🇰","currency":"DKK","latitude":56,"longitude":10},"bank":{"name":"Jyske Bank","url":"www.jyskebank.dk","phone":"+4589893300","city":"Hjørring"}}',
            ],
        ];
    }

    #[DataProvider('success')]
    public function testSuccess(string $baseUrl, string $bin, string $alpha2, string $currency, string $body): void
    {
        $serializer = self::getContainer()->get(SerializerInterface::class);
        $this->mockClient->expects($this->once())
            ->method('get')
            ->with("$baseUrl/$bin")
            ->willReturn(new Response(200, body: $body));

        $client = new UnitUnderTest($baseUrl, $this->mockClient, $serializer);
        $country = $client->lookup($bin);

        $this->assertEquals($alpha2, $country->alpha2);
        $this->assertEquals($currency, $country->currency);
    }

    /** @return array<array<string, string> */
    public static function failure(): array
    {
        return [
            [
                '$baseUrl' => 'https://api.example.com',
                '$bin' => '123456',
            ],
        ];
    }

    #[DataProvider('failure')]
    public function testFailure(string $baseUrl, string $bin): void
    {
        $serializer = self::getContainer()->get(SerializerInterface::class);
        $this->mockClient->expects($this->once())
            ->method('get')
            ->with("$baseUrl/$bin")
            ->willReturn(new Response(404));

        $client = new UnitUnderTest($baseUrl, $this->mockClient, $serializer);
        $this->expectException(NotEncodableValueException::class);
        $client->lookup($bin);
    }
}
