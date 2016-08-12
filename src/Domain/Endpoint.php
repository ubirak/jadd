<?php

namespace Rezzza\Jadd\Domain;

class Endpoint
{
    private $request;

    private $response;

    public function __construct(ApiRequest $request, ApiResponse $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function hasSuccessfulResponse()
    {
        $statusCode = $this->response->getStatusCode();

        return 200 <= $statusCode && $statusCode < 400;
    }

    public static function cleanHeaders(array $headers)
    {
        // Could be simplified with array_filter and ARRAY_FILTER_USE_KEY when dropping PHP 5.5 support
        return array_intersect_key(
            $headers,
            array_flip(
                array_filter(array_keys($headers), function ($key) {
                    $commonHeaders = ['Cache-Control', 'Content-Type', 'Date', 'Content-Encoding', 'Content-Length', 'Host'];

                    return false === in_array($key, $commonHeaders);
                })
            )
        );
    }
}
