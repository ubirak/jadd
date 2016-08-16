<?php

namespace Rezzza\Jadd\Infra\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Http\Client\Common\Plugin;
use Rezzza\Jadd\Domain\EndpointCollector;
use Rezzza\Jadd\Infra\Filesystem\CsvEndpointStorage;

class CollectEndpointPlugin implements Plugin
{
    private $collector;

    public function __construct()
    {
        $this->collector = new EndpointCollector(new CsvEndpointStorage);
    }

    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        return $next($request)->then(function (ResponseInterface $response) use ($request) {
            $this->collector->collect($request, $response);

            return $response;
        });
    }
}
