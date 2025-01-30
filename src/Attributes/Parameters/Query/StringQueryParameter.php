<?php

namespace IsmayilDev\ApiDocKit\Attributes\Parameters\Query;

use Attribute;
use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
class StringQueryParameter extends Parameter
{
    public function __construct(
        string $name,
        ?string $description = null,
        ?string $queryName = null,
        bool $required = false,
        string $example = Generator::UNDEFINED
    ) {
        parent::__construct(
            name: $name,
            description: $description ?? $name,
            in: $queryName,
            required: $required,
            schema: new Schema(type: OpenApiPropertyType::STRING->value),
            example: $example,
        );
    }
}
