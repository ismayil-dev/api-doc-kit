<?php

namespace IsmayilDev\ApiDocKit\Models;

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
    public static function fromModel(string $modelClass): DocEntity
    {
        if (! class_exists($modelClass)) {
            throw new RuntimeException("Model {$modelClass} not found");
        }

        return new DocEntity($modelClass);
    }

    /**
     * @param  string<class-string>  $modelClass
     */
    public function resolve(string $modelClass): DocEntity
    {
        // @TODO Remove this condition when we have a better way to handle with static strings in attributes
        if (! class_exists($modelClass)) {
            throw new RuntimeException("Model {$modelClass} not found");
        }

        return new DocEntity($modelClass);
    }
}
