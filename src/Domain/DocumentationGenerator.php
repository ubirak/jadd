<?php

namespace Rezzza\Jadd\Domain;

class DocumentationGenerator
{
    private $router;

    private $endpointStorage;

    private $coverageChecker;

    private $outputFormatter;

    private $dumper;

    public function __construct(
        EndpointStorage $endpointStorage,
        Router $router,
        CoverageChecker $coverageChecker,
        OutputFormatter $outputFormatter,
        Dumper $dumper
    ) {
        $this->endpointStorage = $endpointStorage;
        $this->router = $router;
        $this->coverageChecker = $coverageChecker;
        $this->outputFormatter = $outputFormatter;
        $this->dumper = $dumper;
    }

    public function generate($routingFile, $outputFile)
    {
        $endpoints = $this->endpointStorage->readAll();

        if (count($endpoints) <= 0) {
            throw new \LogicException('No endpoint collected before running documentation generation. You should use CollectEndpointPlugin on your HttpClient.');
        }

        $routes = $this->router->loadRoutes($endpoints, $routingFile);

        $this->coverageChecker->collectIncompleteRoutes($routes);

        $this->dumper->dump(
            $this->outputFormatter->formatRoutes($routes),
            $outputFile
        );
    }

    public function readCoverage()
    {
        return $this->coverageChecker->readCoverage();
    }
}
