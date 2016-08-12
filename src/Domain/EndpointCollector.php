<?php

namespace Rezzza\Jadd\Domain;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class EndpointCollector
{
    const FILENAME = 'jadd-endpoint-collection';

    private $storage;

    public function __construct(EndpointStorage $storage)
    {
        $this->storage = $storage;
        $this->storage->configureOutput(self::filename());
    }

    public static function filename()
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.self::FILENAME;
    }

    public static function reset()
    {
        file_put_contents(self::filename(), '');
    }

    public function collect(RequestInterface $request, ResponseInterface $response)
    {
        $requestBody = (string) $request->getBody();
        $responseBody = (string) $response->getBody();

        $this->storage->store(
            new Endpoint(
                new ApiRequest(
                    $request->getMethod(),
                    (string) $request->getUri(),
                    $request->getHeader('Content-Type') ?: null,
                    $request->getHeaders(),
                    $requestBody
                ),
                new ApiResponse(
                    $response->getStatusCode(),
                    $response->getHeader('Content-Type') ?: null,
                    $responseBody,
                    $response->getHeaders()
                )
            )
        );
    }

    public function read()
    {
        return $this->storage->readAll();
    }
}
