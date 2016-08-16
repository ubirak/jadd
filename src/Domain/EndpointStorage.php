<?php

namespace Rezzza\Jadd\Domain;

interface EndpointStorage
{
    public function configureOutput($filename);

    public function store(Endpoint $endpoint);

    public function readAll();
}
