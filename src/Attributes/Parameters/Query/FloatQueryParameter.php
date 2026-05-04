<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Parameters\Query;

use Attribute;
use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class FloatQueryParameter extends Parameter
{
    public function __construct(
        string $name,
        ?string $description = null,
        ?string $queryName = null,
        bool $required = false,
        float|string $example = Generator::UNDEFINED,
        ?float $minimum = null,
        ?float $maximum = null,
    ) {
        $schema = new Schema(
            type: OpenApiPropertyType::NUMBER->value,
            format: 'float',
            minimum: $minimum,
            maximum: $maximum,
        );

        parent::__construct(
            name: $name,
            description: $description ?? $name,
            in: $queryName ?? 'query',
            required: $required,
            schema: $schema,
            example: $example,
        );
    }
}
