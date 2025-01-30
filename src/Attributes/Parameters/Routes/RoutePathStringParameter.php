<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Parameters\Routes;

use Attribute;
use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
class RoutePathStringParameter extends Parameter
{
    public function __construct(
        string $name,
        ?string $description = null,
        bool $required = true,
        ?string $example = null
    ) {
        parent::__construct(
            name: $name,
            description: $description,
            in: 'path',
            required: $required,
            schema: new Schema(type: OpenApiPropertyType::STRING->value),
            example: $example,
        );
    }
}
