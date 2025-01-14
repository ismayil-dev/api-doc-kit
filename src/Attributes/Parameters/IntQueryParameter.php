<?php

namespace IsmayilDev\LaravelDocKit\Attributes\Parameters;

use Attribute;
use IsmayilDev\LaravelDocKit\Attributes\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;

class IntQueryParameter extends Parameter
{
    public function __construct(
        string $name,
        ?string $description = null,
        ?string $queryName = null,
        bool $required = false,
        string $example = Generator::UNDEFINED
    )
    {
        parent::__construct(
            name: $name,
            description: $description ?? $name,
            in: $queryName,
            required: $required,
            schema: new Schema(type: OpenApiPropertyType::INTEGER->value),
            example: $example,
        );
    }
}