<?php

namespace Rezzza\Jadd\Ui\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Rezzza\Jadd\Domain\DocumentationGenerator;

class GenerateDoc extends Command
{
    private $documentationGenerator;

    public function __construct(DocumentationGenerator $documentationGenerator)
    {
        parent::__construct();
        $this->documentationGenerator = $documentationGenerator;
    }

    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDefinition([
                new InputArgument('routing_file', InputArgument::REQUIRED, 'path to routing file'),
                new InputArgument('output', InputArgument::REQUIRED, 'path to the output')
            ])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->documentationGenerator->generate(
            $input->getArgument('routing_file'),
            $input->getArgument('output')
        );

        $coverage = $this->documentationGenerator->readCoverage();

        if ($coverage->hasRoutesWithoutValidRequest()) {
            $output->writeln('Missing valid requests (200 <= statusCode < 400):');
            foreach ($coverage->routesWithoutValidRequest() as $routeId) {
                $output->writeln(sprintf('    * %s', $routeId));
            }
        }

        if ($coverage->hasRoutesWithoutAvailableResponses()) {
            $output->writeln('Missing available responses:');
            foreach ($coverage->routesWithoutAvailableResponses() as $routeId) {
                $output->writeln(sprintf('    * %s', $routeId));
            }
        }
    }
}
