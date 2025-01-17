<?php

use OpenApi\Attributes\Get;

class GetTestController
{
    #[Get(path: '/test', description: 'Test description', summary: 'Test')]
    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }
}
