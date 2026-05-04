<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Parameters\Headers;

use Attribute;
use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class StringHeaderParameter extends Parameter
{
    public function __construct(
        string $name,
        ?string $description = null,
        bool $required = false,
        string $example = Generator::UNDEFINED,
    ) {
        parent::__construct(
            name: $name,
            description: $description ?? $name,
            in: 'header',
            required: $required,
            schema: new Schema(type: OpenApiPropertyType::STRING->value),
            example: $example,
        );
    }
}
