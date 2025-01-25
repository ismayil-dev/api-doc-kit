<?php

namespace IsmayilDev\ApiDocKit\Attributes\Properties;

use Attribute;
use IsmayilDev\ApiDocKit\Attributes\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Property;

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
