<?php

namespace IsmayilDev\LaravelDocKit\Resolvers;

use IsmayilDev\LaravelDocKit\Entities\Entity;
use RuntimeException;

readonly class EntityResolver
{
    /**
     * @param string<class-string> $modelClass
     */
    public static function fromModel(string $modelClass): Entity
    {
        if (! class_exists($modelClass)) {
            throw new RuntimeException("Model {$modelClass} not found");
        }

        return new Entity($modelClass);
    }
}
