<?php

namespace Rezzza\Jadd\Domain;

class DocumentationGenerator
{
    private $router;

    private $outputFormatter;

    private $dumper;

    public function __construct(Router $router, OutputFormatter $outputFormatter, Dumper $dumper)
    {
        $this->router = $router;
        $this->outputFormatter = $outputFormatter;
        $this->dumper = $dumper;
    }

    public function generate($routingFile, $outputFile)
    {
        $routes = $this->router->loadRoutes($routingFile);

        if (count($routes) <= 0) {
            throw new \LogicException('No endpoint collected before running documentation generation. You should use CollectEndpointPlugin on your HttpClient.');
        }

        $this->dumper->dump(
            $this->outputFormatter->formatRoutes($routes),
            $outputFile
        );
    }
}
