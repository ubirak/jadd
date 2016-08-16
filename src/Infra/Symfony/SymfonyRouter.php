<?php

namespace Rezzza\Jadd\Infra\Symfony;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCompiler;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Loader\YamlFileLoader;

use Rezzza\Jadd\Domain\EndpointCollector;
use Rezzza\Jadd\Domain\Route;
use Rezzza\Jadd\Domain\Router;

class SymfonyRouter implements Router
{
    private $routeLoader;

    private $endpointCollector;

    public function __construct(YamlFileLoader $routeLoader, EndpointCollector $endpointCollector)
    {
        $this->routeLoader = $routeLoader;
        $this->endpointCollector = $endpointCollector;
    }

    public function loadRoutes($routingFile)
    {
        $routeCollection = $this->routeLoader->load($routingFile);

        $urlMatcher = new UrlMatcher($routeCollection, new RequestContext());

        $endpoints = $this->endpointCollector->read();
        $routes = [];

        foreach ($endpoints as $endpoint) {
            $request = $endpoint->getRequest();
            $urlMatcher->setContext(new RequestContext('', $request->getMethod()));
            $matchedRoute = $urlMatcher->match($request->getUri());
            $routeName = $matchedRoute['_route'];
            $symfonyRoute = $routeCollection->get($matchedRoute['_route']);

            if (false === array_key_exists($routeName, $routes)) {
                $documentation = $symfonyRoute->getOption('_documentation');
                $compiledRoute = RouteCompiler::compile($symfonyRoute);
                $routes[$routeName] = new Route(
                    $routeName,
                    $request->getMethod(),
                    $symfonyRoute->getPath(),
                    $compiledRoute->getPathVariables(),
                    $documentation['description']
                );
            }

            if ($endpoint->hasSuccessfulResponse()) {
                $jsonSchemas = $symfonyRoute->getDefault('_jsonSchema');
                $routes[$routeName]->defineRequest($request, $jsonSchemas['request']);
            }

            $routes[$routeName]->addResponse($endpoint->getResponse());
        }

        return $routes;
    }
}
