<?php

namespace Rezzza\Jadd\Infra\Filesystem;

class FileReader
{
    private $rootDir;

    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    public function read($filename)
    {
        if (false === file_exists($filename)) {
            $filenameRelative = rtrim($this->rootDir, '/').DIRECTORY_SEPARATOR.ltrim($filename, '/');
            if (false === file_exists($filenameRelative)) {
                throw new \LogicException(sprintf('File "%s" does not exists', $filename));
            }
        }

        return file_get_contents($filename);
    }
}
