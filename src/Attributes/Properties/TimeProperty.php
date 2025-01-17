<?php

namespace IsmayilDev\LaravelDocKit\Attributes\Properties;

use Attribute;
use IsmayilDev\LaravelDocKit\Attributes\Enums\OpenApiPropertyFormat;
use IsmayilDev\LaravelDocKit\Attributes\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Property;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
class TimeProperty extends Property
{
    public function __construct(
        ?string $property = null,
        ?string $description = null,
        ?bool $nullable = false,
        ?string $default = null
    ) {
        parent::__construct(
            property: $property,
            description: $description,
            type: OpenApiPropertyType::STRING->value,
            format: OpenApiPropertyFormat::TIME->value,
            default: $default,
            example: '23:15',
            nullable: $nullable
        );
    }
}
