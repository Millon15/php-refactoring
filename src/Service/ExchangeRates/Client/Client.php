<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\ExchangeRates\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Millon\PhpRefactoring\Entity\Collection\Rates;
use Millon\PhpRefactoring\Service\Contracts\ExchangeRatesInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final class Client implements ExchangeRatesInterface
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
     * @link https://exchangeratesapi.io/documentation/
     */
    public function latest(): Rates
    {
        $url = "$this->baseUrl/latest";

        $response = $this->client->get($url);

        return $this->serializer->deserialize(
            $response->getBody()->getContents(),
            Rates::class,
            JsonEncoder::FORMAT
        );
    }
}
