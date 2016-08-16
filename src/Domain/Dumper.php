<?php

namespace Rezzza\Jadd\Domain;

interface Dumper
{
    public function dump($content, $filename);
}
