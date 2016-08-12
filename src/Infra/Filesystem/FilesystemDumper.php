<?php

namespace Rezzza\Jadd\Infra\Filesystem;

use Rezzza\Jadd\Domain\Dumper;

class FilesystemDumper implements Dumper
{
    public function dump($content, $filename)
    {
        file_put_contents($filename, $content);
    }
}
