<?php

namespace Rezzza\Jadd\Infra\Symfony;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCompiler;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

use Rezzza\Jadd\Domain\Route;
use Rezzza\Jadd\Domain\Router;

class SymfonyRouter implements Router
{
    private $routeLoader;

    public function __construct(YamlFileLoader $routeLoader)
    {
        $this->routeLoader = $routeLoader;
    }

    public function loadRoutes(array $endpoints, $routingFile)
    {
        $routeCollection = $this->routeLoader->load($routingFile);

        $urlMatcher = new UrlMatcher($routeCollection, new RequestContext());

        $routes = [];

        foreach ($routeCollection as $routeName => $symfonyRoute) {
            if (false === array_key_exists($routeName, $routes)) {
                $documentation = $symfonyRoute->getOption('_documentation');
                $compiledRoute = RouteCompiler::compile($symfonyRoute);
                $routes[$routeName] = new Route(
                    $routeName,
                    $symfonyRoute->getPath(),
                    $compiledRoute->getPathVariables(),
                    $documentation['description']
                );
            }

            $endpointsMatched = array_values( // only here to read numeric keys
                array_filter(
                $endpoints,
                function ($endpoint) use ($urlMatcher, $routeName) {
                    $request = $endpoint->getRequest();
                    $urlMatcher->setContext(new RequestContext('', $request->getMethod()));
                    try {
                        $matchedRoute = $urlMatcher->match($request->getUri());

                        return $matchedRoute['_route'] === $routeName;
                    } catch (ResourceNotFoundException $exception) {
                        return false;
                    }
                }
            ));

            foreach ($endpointsMatched as $index => $endpoint) {
                if ($index < 1) {
                    $routes[$routeName]->defineMethod($endpoint->getRequest()->getMethod());
                }

                if ($endpoint->hasSuccessfulResponse()) {
                    $jsonSchemas = $symfonyRoute->getDefault('_jsonSchema');
                    $routes[$routeName]->defineSuccessfulRequest($endpoint->getRequest(), $jsonSchemas['request']);
                }

                $routes[$routeName]->addResponse($endpoint->getResponse());
            }
        }

        return $routes;
    }
}
