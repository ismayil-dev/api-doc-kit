<?php

namespace IsmayilDev\LaravelDocKit\Attributes\Properties;

use OpenApi\Attributes\Property;

use IsmayilDev\LaravelDocKit\Attributes\Enums\OpenApiPropertyType;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
class NumberProperty extends Property
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
            type: OpenApiPropertyType::NUMBER->value,
            format: $format,
            default: $default,
            example: $example ?? 2.5,
            nullable: $nullable
        );
    }
}