<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Parameters\Query;

use Attribute;
use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class BoolQueryParameter extends Parameter
{
    public function __construct(
        string $name,
        ?string $description = null,
        ?string $queryName = null,
        bool $required = false,
        bool|string $example = Generator::UNDEFINED,
    ) {
        parent::__construct(
            name: $name,
            description: $description ?? $name,
            in: $queryName,
            required: $required,
            schema: new Schema(type: OpenApiPropertyType::BOOLEAN->value),
            example: $example,
        );
    }
}
