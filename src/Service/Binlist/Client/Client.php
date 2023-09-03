<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\Binlist\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Millon\PhpRefactoring\Entity\Country;
use Millon\PhpRefactoring\Service\Contracts\BinLookUpInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

final class Client implements BinLookUpInterface
{
    private readonly HttpClient $client;

    public function __construct(
        private readonly string $baseUrl,
        private readonly SerializerInterface $serializer,
        array $config = [],
    ) {
        $this->client = new HttpClient($config);
    }

    /**
     * @throws GuzzleException
     * @link https://binlist.net/
     */
    public function lookup(string $bin): Country
    {
        $url = "$this->baseUrl/$bin";

        $response = $this->client->post($url);

        return $this->serializer->deserialize(
            $response->getBody()->getContents(),
            Country::class,
            JsonEncoder::FORMAT,
        );
    }
}
