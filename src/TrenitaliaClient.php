<?php

declare(strict_types=1);

namespace Calavera\TrenitaliaClient;

use DateTime;
use InvalidArgumentException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Client
{
    public function __construct(
        protected ClientInterface $client,
        protected RequestFactoryInterface $requestFactory,
        protected StreamFactoryInterface $streamFactory
    ) {
    }

    public function getStations(string $name, ?int $limit = null): ResponseInterface
    {
        $uri = "https://www.lefrecce.it/Channels.Website.BFF.WEB/website/locations/search?name=" . urlencode($name);

        if ($limit !== null) {
            $uri .= "&limit=" . urlencode((string)$limit);
        }

        $r = $this->requestFactory->createRequest('GET', $uri);

        return $this->client->sendRequest($r);
    }

    public function getSolutions(string $from, string $to, string $dt): ResponseInterface
    {
        $departureDateTime = DateTime::createFromFormat(\DateTime::ATOM, $dt);

        if (!$departureDateTime || $departureDateTime->format(\DateTime::ATOM) !== $dt) {
            throw new InvalidArgumentException('Invalid departure time format. It should be in ISO 8601 format.');
        }

        $body = [
            'departureLocationId' => $from,
            'arrivalLocationId' => $to,
            'departureTime' => $dt,
            'adults' => 1
        ];

        $uri = 'https://www.lefrecce.it/Channels.Website.BFF.WEB/website/ticket/solutions';

        $r = $this->requestFactory->createRequest('POST', $uri)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(
                $this->streamFactory->createStream(json_encode($body))
            );
        $body = [
            'departureLocationId' => $from,
            'arrivalLocationId' => $to,
            'departureTime' => $dt,
            'adults' => 1
        ];

        return $this->client->sendRequest($r);
    }
}
