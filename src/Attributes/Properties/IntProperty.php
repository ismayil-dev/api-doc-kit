<?php

namespace IsmayilDev\LaravelDocKit\Attributes\Properties;

use IsmayilDev\LaravelDocKit\Attributes\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Property;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
class IntProperty extends Property
{
    public function __construct(
        string $description,
        ?string $property = null,
        ?string $example = null,
        ?bool $nullable = false,
        ?string $format = null,
        ?string $default = null
    ) {
        parent::__construct(
            property: $property,
            description: $description,
            type: OpenApiPropertyType::INTEGER->value,
            format: $format,
            default: $default,
            example: $example ?? 100,
            nullable: $nullable
        );
    }
}