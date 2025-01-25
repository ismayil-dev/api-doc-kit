<?php

namespace IsmayilDev\ApiDocKit\Attributes\Properties;

use Attribute;
use IsmayilDev\ApiDocKit\Attributes\Enums\OpenApiPropertyFormat;
use IsmayilDev\ApiDocKit\Attributes\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Property;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
class DateTimeProperty extends Property
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
            format: OpenApiPropertyFormat::DATE_TIME->value,
            default: $default,
            example: '2024-06-12T09:39:49.000000Z',
            nullable: $nullable
        );
    }
}
