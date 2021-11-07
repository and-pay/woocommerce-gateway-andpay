<?php

declare(strict_types=1);

namespace Andpay\HttpClient\Message;

use Psr\Http\Message\ResponseInterface;

final class ResponseMediator
{
    public static function getContent(ResponseInterface $response): array
    {
        $response = json_decode($response->getBody()->getContents(), true);

        if(isset($response['data'])) {
            return $response['data'];
        }

        return $response;
    }
}
