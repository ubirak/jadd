<?php

namespace Rezzza\Jadd\Tests\Units\Domain;

use mageekguy\atoum;

class ApiResponse extends atoum
{
    public function test_it_merge_new_body_with_current_body()
    {
        $this
            ->given(
                $this->newTestedInstance(200, null, '{"name": "test"}')
            )
            ->when(
                $this->testedInstance->mergeBody('{"id": "123"}')
            )
            ->then
                ->variable($this->testedInstance->getBody())
                    ->isEqualTo('{"name":"test","id":"123"}')
        ;
    }

    public function test_it_merge_body_even_with_empty_body()
    {
        $this
            ->given(
                $this->newTestedInstance(200)
            )
            ->when(
                $this->testedInstance->mergeBody('{"id": "123"}')
            )
            ->then
                ->variable($this->testedInstance->getBody())
                    ->isEqualTo('{"id":"123"}')
        ;
    }

    public function test_it_remove_empty_property_from_body()
    {
        $this
            ->when(
                $this->newTestedInstance(200, null, '{"name": "test", "id": ""}')
            )
            ->then
                ->variable($this->testedInstance->getBody())
                    ->isEqualTo('{"name":"test"}')
        ;
    }
}
