<?php

namespace Rezzza\Jadd\Infra\Filesystem;

use Rezzza\Jadd\Domain\ApiRequest;
use Rezzza\Jadd\Domain\ApiResponse;
use Rezzza\Jadd\Domain\Endpoint;
use Rezzza\Jadd\Domain\EndpointStorage;

class CsvEndpointStorage implements EndpointStorage
{
    private $filename;

    public function __construct($filename)
    {
        if (null !== $filename) {
            $this->configureOutput($filename);
        }
    }

    public function configureOutput($filename)
    {
        $this->filename = $filename;
    }

    public function store(Endpoint $endpoint)
    {
        $request = $endpoint->getRequest();
        $response = $endpoint->getResponse();
        $handle = fopen($this->filename, 'a');
        fputcsv(
            $handle,
            [
                $request->getMethod(),
                $request->getUri(),
                $request->getContentType(),
                json_encode($request->getHeaders()),
                $request->getBody(),
                $response->getStatusCode(),
                $response->getContentType(),
                json_encode($response->getHeaders()),
                $response->getBody(),
            ]
        );
        fclose($handle);
    }

    public function readAll()
    {
        $endpoints = [];
        $handle = fopen($this->filename, 'r');
        while (($data = fgetcsv($handle)) !== false) {
            $endpoints[] = new Endpoint(
                new ApiRequest($data[0], $data[1], $data[2], json_decode($data[3], true), $data[4]),
                new ApiResponse($data[5], $data[6], $data[8], json_decode($data[7], true))
            );
        }
        fclose($handle);

        return $endpoints;
    }
}
