<?php

declare(strict_types=1);

namespace Andpay\Endpoint;

use Andpay\HttpClient\Message\ResponseMediator;
use Andpay\Client;
use Andpay\Exception\MissingArgumentException;

final class Currencies
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function all(): array
    {
        return ResponseMediator::getContent($this->client->getHttpClient()->get('/currencies/'));
    }

    public function get($shortName = null): array
    {
        if(is_null($shortName)) {
            throw new MissingArgumentException('shortName');
        }

        return ResponseMediator::getContent($this->client->getHttpClient()->get('/currencies/' . $shortName . '/'));
    }


}
