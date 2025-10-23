<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Exceptions;

use Exception;

/**
 * Thrown when an invalid array item type is specified in ArrayOf attribute
 */
class InvalidArrayItemTypeException extends Exception
{
    /**
     * Create exception for non-existent class
     */
    public static function classNotFound(string $className, string $dtoClass, string $propertyName): self
    {
        $shortClassName = class_basename($className);
        $shortDtoName = class_basename($dtoClass);

        return new self(
            "Class '{$shortClassName}' specified in #[ArrayOf({$shortClassName}::class)] ".
            "for property '{$propertyName}' in '{$shortDtoName}' does not exist. ".
            'Ensure the class is defined and properly imported.'
        );
    }

    /**
     * Create exception for class without DataSchema attribute
     */
    public static function missingDataSchemaAttribute(string $className, string $dtoClass, string $propertyName): self
    {
        $shortClassName = class_basename($className);
        $shortDtoName = class_basename($dtoClass);

        return new self(
            "Class '{$shortClassName}' used in #[ArrayOf({$shortClassName}::class)] ".
            "for property '{$propertyName}' in '{$shortDtoName}' must have the #[DataSchema] attribute. ".
            "Add #[DataSchema] to the {$shortClassName} class to generate its OpenAPI schema."
        );
    }
}
