<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Exceptions;

use RuntimeException;

/**
 * Exception thrown when an enum is used in a DataSchema but doesn't have the #[Enum] attribute
 */
class MissingEnumAttributeException extends RuntimeException
{
    public static function create(string $enumClass, string $dtoClass, string $propertyName): self
    {
        $enumName = class_basename($enumClass);
        $dtoName = class_basename($dtoClass);

        return new self(
            "Enum '{$enumName}' is used in property '{$propertyName}' of '{$dtoName}' ".
            "but doesn't have the #[Enum] attribute. ".
            "Add #[Enum] to the {$enumName} class to generate proper OpenAPI schema."
        );
    }
}
