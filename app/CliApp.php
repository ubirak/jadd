<?php

use Symfony\Component\Console\Application as Console;
use Rezzza\Jadd\Ui\Cli\GenerateDocCli;

class CliApp
{
    private $kernel;

    private $console;

    public function __construct(Kernel $kernel, Console $console)
    {
        $this->kernel = $kernel;
        $this->console = $console;
    }

    public function run()
    {
        $container = $this->kernel->boot();
        foreach ($container->get('cli') as $cli) {
            $this->console->add($cli);
        }
        $this->console->run();
    }
}
