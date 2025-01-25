<?php

namespace IsmayilDev\ApiDocKit\Resolvers;

use IsmayilDev\ApiDocKit\Entities\Entity;
use RuntimeException;

readonly class EntityResolver
{
    /**
     * @param  string<class-string>  $modelClass
     *
     * @TODO Kill this static method
     *
     * @deprecated Please use resolve method after inject EntityResolver
     */
    public static function fromModel(string $modelClass): Entity
    {
        if (! class_exists($modelClass)) {
            throw new RuntimeException("Model {$modelClass} not found");
        }

        return new Entity($modelClass);
    }

    /**
     * @param  string<class-string>  $modelClass
     */
    public function resolve(string $modelClass): Entity
    {
        // @TODO Remove this condition when we have a better way to handle with static strings in attributes
        if (! class_exists($modelClass)) {
            throw new RuntimeException("Model {$modelClass} not found");
        }

        return new Entity($modelClass);
    }
}
