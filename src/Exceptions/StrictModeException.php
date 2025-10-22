<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Exceptions;

use RuntimeException;

/**
 * Exception thrown when strict mode is enabled and a computed field
 * cannot be auto-detected and is not explicitly defined.
 */
class StrictModeException extends RuntimeException
{
    public static function undefinedComputedField(string $fieldName, string $className): self
    {
        return new self(
            "Strict mode: Computed field '{$fieldName}' in {$className} has unknown type. ".
            'Please define it explicitly using property attributes in #[DataSchema(properties: [...])]. '.
            'Alternatively, disable strict mode in config/api-doc-kit.php.'
        );
    }
}
