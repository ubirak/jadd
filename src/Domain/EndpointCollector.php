<?php

namespace Rezzza\Jadd\Domain;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class EndpointCollector
{
    const FILENAME = 'jadd-endpoint-collection';

    private $storage;

    private $filteredHeaders = ['Cache-Control', 'Content-Type', 'Date', 'Content-Encoding', 'Content-Length', 'Host'];

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

    public function addHeadersToFilter(array $headers)
    {
        $this->filteredHeaders = array_merge($this->filteredHeaders, $headers);
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
                    $this->cleanHeaders($request->getHeaders()),
                    $requestBody
                ),
                new ApiResponse(
                    $response->getStatusCode(),
                    $response->getHeader('Content-Type') ?: null,
                    $responseBody,
                    $this->cleanHeaders($response->getHeaders())
                )
            )
        );
    }

    public function read()
    {
        return $this->storage->readAll();
    }

    private function cleanHeaders(array $headers)
    {
        // Could be simplified with array_filter and ARRAY_FILTER_USE_KEY when dropping PHP 5.5 support
        return array_intersect_key(
            $headers,
            array_flip(
                array_filter(array_keys($headers), function ($key) {
                    return false === in_array($key, $this->filteredHeaders);
                })
            )
        );
    }
}
