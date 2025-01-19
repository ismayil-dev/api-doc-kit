<?php

namespace IsmayilDev\LaravelDocKit\Traits;

use IsmayilDev\LaravelDocKit\Resolvers\EntityResolver;

trait ResourceTrait
{
    public function getEntity(): EntityResolver
    {
        return EntityResolver::fromModel($this->model);
    }
}
