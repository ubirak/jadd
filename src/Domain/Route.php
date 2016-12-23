<?php

namespace Rezzza\Jadd\Domain;

class Route
{
    private $id;

    private $method;

    private $description;

    private $path;

    private $variables;

    private $request;

    private $availableResponses = [];

    public function __construct($id, $path, $variables, $description)
    {
        $this->id = $id;
        $this->path = $path;
        $this->variables = $variables;
        $this->description = $description;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getVariables()
    {
        return $this->variables;
    }

    public function defineMethod($method)
    {
        $this->method = $method;
    }

    public function defineSuccessfulRequest(ApiRequest $request, $jsonSchema)
    {
        $request->setJsonSchema($jsonSchema);
        $this->request = $request;
    }

    public function addResponse(ApiResponse $response)
    {
        if (array_key_exists($response->getStatusCode(), $this->availableResponses)) {
            $this->availableResponses[$response->getStatusCode()]->mergeBody($response->getBody());
        } else {
            $this->availableResponses[$response->getStatusCode()] = $response;
        }
    }

    public function getAvailableResponses()
    {
        return $this->availableResponses;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
