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
}
