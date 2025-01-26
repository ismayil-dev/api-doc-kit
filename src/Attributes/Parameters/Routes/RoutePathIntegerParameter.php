<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Parameters\Routes;

use Attribute;
use IsmayilDev\ApiDocKit\Attributes\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
class RoutePathIntegerParameter extends Parameter
{
    public function __construct(
        string $name,
        ?string $description = null,
        bool $required = true,
        ?int $example = null
    ) {
        parent::__construct(
            name: $name,
            description: $description,
            in: 'path',
            required: $required,
            schema: new Schema(type: OpenApiPropertyType::INTEGER->value),
            example: $example,
        );
    }
}
