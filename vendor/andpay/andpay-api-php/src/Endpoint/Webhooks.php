<?php

declare(strict_types=1);

namespace Andpay\Endpoint;

use Andpay\HttpClient\Message\ResponseMediator;
use Andpay\Client;
use Andpay\Exception\MissingArgumentException;

final class Webhooks
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function all(): array
    {
        return ResponseMediator::getContent($this->client->getHttpClient()->get('/webhooks/'));
    }

    public function create($webhookData = null) : array
    {
        if(is_null($webhookData)) {
            throw new MissingArgumentException('webhookData');
        }

        return ResponseMediator::getContent($this->client->getHttpClient()->post('/webhooks/', [], json_encode($webhookData)));
    }

    public function delete($webhookId = null) : array
    {
        if(is_null($webhookId)) {
            throw new MissingArgumentException('webhookId');
        }

        return ResponseMediator::getContent($this->client->getHttpClient()->delete('/webhooks/', []));
    }


}
