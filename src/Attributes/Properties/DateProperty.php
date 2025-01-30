<?php

namespace IsmayilDev\ApiDocKit\Attributes\Properties;

use Attribute;
use Illuminate\Support\Str;
use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyFormat;
use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Property;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
class DateProperty extends Property
{
    public function __construct(
        ?string $property = null,
        ?string $description = null,
        ?bool $nullable = false,
        ?string $default = null
    ) {
        parent::__construct(
            property: $property,
            description: $description ?? Str::title(Str::snake($property, ' ')),
            type: OpenApiPropertyType::STRING->value,
            format: OpenApiPropertyFormat::DATE->value,
            default: $default,
            example: '2023-12-05',
            nullable: $nullable
        );
    }
}
