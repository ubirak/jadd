<?php

namespace Rezzza\Jadd\Domain;

class Coverage
{
    private $routesWithoutValidRequest = [];

    private $routesWithoutAvailableResponses = [];

    public function __construct(array $routesWithoutValidRequest, array $routesWithoutAvailableResponses)
    {
        $this->routesWithoutValidRequest = $routesWithoutValidRequest;
        $this->routesWithoutAvailableResponses = $routesWithoutAvailableResponses;
    }

    public function hasRoutesWithoutValidRequest()
    {
        return 0 < count($this->routesWithoutValidRequest);
    }

    public function hasRoutesWithoutAvailableResponses()
    {
        return 0 < count($this->routesWithoutAvailableResponses);
    }

    public function routesWithoutValidRequest()
    {
        return $this->routesWithoutValidRequest;
    }

    public function routesWithoutAvailableResponses()
    {
        return $this->routesWithoutAvailableResponses;
    }
}
