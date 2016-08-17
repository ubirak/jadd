<?php

namespace Rezzza\Jadd\Tests\Units\Infra\Filesystem;

use mageekguy\atoum;

class FileReader extends atoum
{
    public function test_it_supports_absolute_path()
    {
        $this
            ->given(
                $this->newTestedInstance('/var'),
                $this->function->file_exists = true,
                $this->function->file_get_contents = 'my content'
            )
            ->when(
                $content = $this->testedInstance->read('app/config/file.json')
            )
            ->then
                ->function('file_exists')->wasCalled()->once()
                ->function('file_exists')->wasCalledWithArguments('app/config/file.json')->once()
                ->variable($content)->isEqualTo('my content')
        ;
    }

    public function test_it_supports_relative_path()
    {
        $this
            ->given(
                $this->newTestedInstance('/var'),
                $this->function->file_exists[1] = false,
                $this->function->file_exists[2] = true,
                $this->function->file_get_contents = 'my content'
            )
            ->when(
                $content = $this->testedInstance->read('app/config/file.json')
            )
            ->then
                ->function('file_exists')->wasCalledWithArguments('app/config/file.json')->once()
                ->function('file_exists')->wasCalledWithArguments('/var/app/config/file.json')->once()
                ->function('file_exists')->wasCalled()->twice()
                ->variable($content)->isEqualTo('my content')
        ;
    }

    public function test_it_leads_to_exception_if_file_does_not_exist()
    {
        $this
            ->given(
                $this->newTestedInstance('/var'),
                $this->function->file_exists = false
            )
            ->exception(function () {
                $this->testedInstance->read('app/config/file.json');
            })
                ->hasMessage('File "app/config/file.json" does not exists')
        ;
    }
}
