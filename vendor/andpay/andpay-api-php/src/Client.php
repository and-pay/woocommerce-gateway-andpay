<?php

declare(strict_types=1);

namespace Andpay;

use Andpay\Endpoint;
use Andpay\ClientBuilder;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin\HeaderDefaultsPlugin;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Message\UriFactory;

final class Client
{
    private $clientBuilder;
    private $accessToken;

    public function __construct($accessToken = null, ClientBuilder $clientBuilder = null, UriFactory $uriFactory = null)
    {
        $this->accessToken = $accessToken;

        $this->clientBuilder = $clientBuilder ?: new ClientBuilder();
        $uriFactory = $uriFactory ?: Psr17FactoryDiscovery::findUriFactory();

        $this->clientBuilder->addPlugin(
            new BaseUriPlugin($uriFactory->createUri('https://andpay.io/api/v1/'))
        );
        $this->clientBuilder->addPlugin(
            new HeaderDefaultsPlugin(
                [
                    'User-Agent' => 'Andpay-api-php',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ]
            )
        );
    }

    public function getHttpClient(): HttpMethodsClientInterface
    {
        return $this->clientBuilder->getHttpClient();
    }

    /**
    * Endpoints
    */
    public function currencies(): Endpoint\Currencies
    {
        return new Endpoint\Currencies($this);
    }

    public function payments(): Endpoint\Payments
    {
        return new Endpoint\Payments($this);
    }

    public function transactions(): Endpoint\Transactions
    {
        return new Endpoint\Transactions($this);
    }

    public function webhooks(): Endpoint\Webhooks
    {
        return new Endpoint\Webhooks($this);
    }

}
