<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Parameters\Query;

use Attribute;
use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class SearchQueryParameter extends Parameter
{
    public function __construct(
        string $name = 'q',
        ?string $description = null,
        bool $required = false,
        ?int $minLength = null,
        ?int $maxLength = null,
        string $example = Generator::UNDEFINED,
    ) {
        parent::__construct(
            name: $name,
            description: $description ?? 'Free-text search across configured columns',
            in: 'query',
            required: $required,
            schema: new Schema(
                type: OpenApiPropertyType::STRING->value,
                minLength: $minLength,
                maxLength: $maxLength,
            ),
            example: $example,
        );
    }
}
