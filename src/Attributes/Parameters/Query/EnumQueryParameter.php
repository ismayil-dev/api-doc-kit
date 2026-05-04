<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Parameters\Query;

use Attribute;
use IsmayilDev\ApiDocKit\Helper\EnumSchemaHelper;
use OpenApi\Attributes\Parameter;
use OpenApi\Generator;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class EnumQueryParameter extends Parameter
{
    /**
     * @param  class-string<\BackedEnum>  $enumClass
     */
    public function __construct(
        string $name,
        string $enumClass,
        ?string $description = null,
        ?string $queryName = null,
        bool $required = false,
        string $example = Generator::UNDEFINED,
    ) {
        parent::__construct(
            name: $name,
            description: $description ?? $name,
            in: $queryName,
            required: $required,
            schema: EnumSchemaHelper::buildSchema($enumClass),
            example: $example,
        );
    }
}
