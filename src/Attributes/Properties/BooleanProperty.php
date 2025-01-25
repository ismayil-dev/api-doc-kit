<?php

namespace IsmayilDev\ApiDocKit\Attributes\Properties;

use Attribute;
use Illuminate\Support\Str;
use IsmayilDev\ApiDocKit\Attributes\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Property;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
class BooleanProperty extends Property
{
    public function __construct(
        ?string $property = null,
        ?string $description = null,
        bool $example = true,
    ) {
        parent::__construct(
            property: $property,
            description: $description ?? Str::title(Str::snake($property, ' ')),
            type: OpenApiPropertyType::BOOLEAN->value,
            example: $example,
        );
    }
}
