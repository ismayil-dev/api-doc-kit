<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Helper;

use BackedEnum;
use InvalidArgumentException;
use OpenApi\Attributes\Schema;
use ReflectionEnum;

/**
 * Builds an OpenAPI Schema instance from a backed PHP enum class.
 *
 * Mirrors what swagger-php emits when it scans an #[Enum]-decorated enum,
 * but usable inline for query parameters and other ad-hoc spots where
 * referencing a component schema isn't available.
 */
final class EnumSchemaHelper
{
    /**
     * @param  class-string<BackedEnum>  $enumClass
     */
    public static function buildSchema(string $enumClass): Schema
    {
        if (! is_subclass_of($enumClass, BackedEnum::class)) {
            throw new InvalidArgumentException(
                "EnumSchemaHelper expects a BackedEnum class, got: {$enumClass}"
            );
        }

        $reflection = new ReflectionEnum($enumClass);
        $backingType = (string) $reflection->getBackingType();

        $schemaType = $backingType === 'int' ? 'integer' : 'string';
        $values = array_map(fn (BackedEnum $case) => $case->value, $enumClass::cases());
        $names = array_map(fn (BackedEnum $case) => $case->name, $enumClass::cases());

        $schema = new Schema(
            type: $schemaType,
            enum: $values,
        );

        if ($backingType === 'int') {
            $schema->x = ['enum-varnames' => $names];
        }

        return $schema;
    }
}
