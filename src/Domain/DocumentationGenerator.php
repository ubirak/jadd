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
        $this->dumper->dump(
            $this->outputFormatter->formatRoutes($this->router->loadRoutes($routingFile)),
            $outputFile
        );
    }
}
