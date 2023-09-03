<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\Binlist\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Millon\PhpRefactoring\Service\Contracts\BinLookUpInterface;

final class Client implements BinLookUpInterface
{
    private readonly HttpClient $client;

    public function __construct(
        private readonly string $baseUrl,
        array $config = [],
    ) {
        $this->client = new HttpClient($config);
    }

    /**
     * @throws GuzzleException
     * @link https://smartsendereu.atlassian.net/wiki/spaces/docsru/pages/97386531/Contact+Tags+API
     */
    public function lookup(string $bin): void
    {
        $url = $this->baseUrl . "/$bin";

        $responseTag = $this->client->post($url);
    }
}
