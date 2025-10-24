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
     * Resolve entity from model class or static string
     *
     * @param  string  $entity  Model class (e.g., User::class) or static string (e.g., 'Product')
     * @param  string|null  $keyType  Optional key type override for static strings (default: 'int')
     * @param  string|int|null  $exampleId  Optional example ID override
     */
    public function resolve(string $entity, ?string $keyType = null, string|int|null $exampleId = null): DocEntity
    {
        return new DocEntity(
            entity: $entity,
            keyType: $keyType,
            exampleId: $exampleId
        );
    }
}
