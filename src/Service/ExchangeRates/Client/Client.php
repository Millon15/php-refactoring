<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Service\ExchangeRates\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Millon\PhpRefactoring\Service\Contracts\ExchangeRatesInterface;

final class Client implements ExchangeRatesInterface
{
    private readonly HttpClient $client;

    public function __construct(
        private readonly string $baseUrl,
        array $config = [],
    ) {
        $this->client = new HttpClient($config);
    }

    /**
     * @return array<string, string|array<string, mixed>>
     *
     * @throws GuzzleException
     * @link https://smartsendereu.atlassian.net/wiki/spaces/docsru/pages/97288213/Messages+API
     */
    public function latest(): array
    {
        $url = $this->baseUrl . "/latest";

        $response = $this->client->get($url);

        // TODO check in middleware $response->getStatusCode();
        // TODO add primitive validation in middleware of $response->getBody();

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @throws GuzzleException
     * @link https://smartsendereu.atlassian.net/wiki/spaces/docsru/pages/97288213/Messages+API
     */
    public function sendContactMessage(string $contactId, string $message): void
    {
        $url = $this->baseUrl . "/contacts/$contactId/send";

        $responseMessage = $this->client->post($url, [
            RequestOptions::JSON => [
                'type' => MessageType::TEXT->value,
                'content' => $message,
                // watermark = milliseconds since UNIX epoch
                'watermark' => round(microtime(true) * 1000),
            ],
        ]);
    }

    /**
     * @throws GuzzleException
     * @link https://smartsendereu.atlassian.net/wiki/spaces/docsru/pages/97386531/Contact+Tags+API
     */
    public function addContactTag(string $contactId, Tag $tag): void
    {
        $url = $this->baseUrl . "/contacts/$contactId/tags/$tag->value";

        $responseTag = $this->client->post($url);
    }
}
