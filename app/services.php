<?php

use Rezzza\Jadd\Domain;
use Rezzza\Jadd\Ui;
use Rezzza\Jadd\Infra\APIBlueprintFormatter;
use Rezzza\Jadd\Infra\Filesystem\CsvEndpointStorage;
use Rezzza\Jadd\Infra\Filesystem\FilesystemDumper;
use Rezzza\Jadd\Infra\Symfony\SymfonyRouter;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Interop\Container\ContainerInterface;

return [
    'working.dir' => getcwd(),
    'cli' => [
        \DI\object(Ui\Cli\GenerateDoc::class)
            ->constructor(
                \DI\get('documentation_generator.jadd.rezzza')
            )
    ],
    'documentation_generator.jadd.rezzza' => function (ContainerInterface $c) {
        return new Domain\DocumentationGenerator(
            new SymfonyRouter(
                new YamlFileLoader(new FileLocator($c->get('working.dir'))),
                new Domain\EndpointCollector(new CsvEndpointStorage())
            ),
            new APIBlueprintFormatter($c->get('working.dir')),
            new FilesystemDumper
        );
    },
];
