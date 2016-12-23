<?php

namespace Rezzza\Jadd\Domain;

class CoverageChecker
{
    private $routesWithoutValidRequest = [];

    private $routesWithoutAvailableResponses = [];

    public function collectIncompleteRoutes(array $routes)
    {
        foreach ($routes as $route) {
            if (null === $route->getRequest()) {
                $this->routesWithoutValidRequest[] = $route->getId();
            }

            if (count($route->getAvailableResponses()) <= 0) {
                $this->routesWithoutAvailableResponses[] = $route->getId();
            }
        }
    }

    public function readCoverage()
    {
        return new Coverage($this->routesWithoutValidRequest, $this->routesWithoutAvailableResponses);
    }
}
