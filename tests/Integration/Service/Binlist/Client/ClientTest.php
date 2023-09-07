<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Integration\Service\Binlist\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;
use Millon\PhpRefactoring\Service\Binlist\Client\Client as UnitUnderTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
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
    public static function successJson(): array
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

    #[DataProvider('successJson')]
    public function testSuccess(string $baseUrl, string $bin, string $alpha2, string $currency, string $body): void
    {
        $serializer = self::getContainer()->get(SerializerInterface::class);
        $this->mockClient->expects($this->once())
            ->method('get')
            ->with("$baseUrl/$bin")
            ->willReturn(new Response(200, [], $body));

        $client = new UnitUnderTest($baseUrl, $this->mockClient, $serializer);
        $country = $client->lookup($bin);

        $this->assertEquals($alpha2, $country->alpha2);
        $this->assertEquals($currency, $country->currency);
    }
}
