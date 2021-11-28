<?php

declare(strict_types=1);

namespace Andpay\Endpoint;

use Andpay\HttpClient\Message\ResponseMediator;
use Andpay\Client;
use Andpay\Exception\MissingArgumentException;

final class Transactions
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function all(): array
    {
        return ResponseMediator::getContent($this->client->getHttpClient()->get('/transactions/'));
    }

    public function get($transactionId = null) : array
    {
        if(is_null($transactionId)) {
            throw new MissingArgumentException('transactionId');
        }

        return ResponseMediator::getContent($this->client->getHttpClient()->get('/transactions/' . $transactionId . '/'));
    }

}
