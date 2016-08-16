<?php

class Kernel
{
    private $container;

    private $booted = false;

    public function boot()
    {
        if (true === $this->booted) {
            return $this->container;
        }

        $builder = new \DI\ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->useAnnotations(false);
        $builder->addDefinitions(__DIR__.'/services.php');

        $this->container = $builder->build();
        $this->booted = true;

        return $this->container;
    }
}
