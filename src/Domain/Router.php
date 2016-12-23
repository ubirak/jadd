<?php

namespace Rezzza\Jadd\Domain;

interface Router
{
    public function loadRoutes(array $endpoints, $routingFile);
}
