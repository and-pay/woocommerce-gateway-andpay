<?php

declare(strict_types=1);

namespace Andpay\Endpoint;

use Andpay\HttpClient\Message\ResponseMediator;
use Andpay\Client;
use Andpay\Exception\MissingArgumentException;

final class Payments
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function all() : array
    {
        return ResponseMediator::getContent($this->client->getHttpClient()->get('/payments/'));
    }

    public function create($paymentData = null) : array
    {
        if(is_null($paymentData)) {
            throw new MissingArgumentException('paymentData');
        }

        return ResponseMediator::getContent($this->client->getHttpClient()->post('/payments/', [], json_encode($paymentData)));
    }

    public function get($paymentId = null) : array
    {
        if(is_null($paymentId)) {
            throw new MissingArgumentException('paymentId');
        }

        return ResponseMediator::getContent($this->client->getHttpClient()->get('/payments/' . $paymentId . '/'));
    }

    public function transactions($paymentId = null) : array
    {
        if(is_null($paymentId)) {
            throw new MissingArgumentException('paymentId');
        }

        return ResponseMediator::getContent($this->client->getHttpClient()->get('/payments/' . $paymentId . '/transactions/'));
    }


}
