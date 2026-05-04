<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Parameters\Query;

use Attribute;
use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;

/**
 * Documents an array-valued query parameter (e.g. ?ids[]=1&ids[]=2).
 *
 * Uses style=form, explode=true to match Laravel's array-bracket convention.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ArrayQueryParameter extends Parameter
{
    /**
     * @param  string  $itemType  One of OpenApiPropertyType values: string, integer, number, boolean
     * @param  string|null  $itemFormat  Optional format for items (e.g. 'date', 'uuid')
     * @param  list<string|int>|null  $itemEnum  Optional enum constraint for items
     */
    public function __construct(
        string $name,
        string $itemType = 'string',
        ?string $itemFormat = null,
        ?array $itemEnum = null,
        ?string $description = null,
        ?string $queryName = null,
        bool $required = false,
        ?int $minItems = null,
        ?int $maxItems = null,
        string|array $example = Generator::UNDEFINED,
    ) {
        $items = new Items(
            type: $itemType,
            format: $itemFormat,
            enum: $itemEnum,
        );

        $schema = new Schema(
            type: OpenApiPropertyType::ARRAY->value,
            items: $items,
            minItems: $minItems,
            maxItems: $maxItems,
        );

        parent::__construct(
            name: $name,
            description: $description ?? $name,
            in: $queryName ?? 'query',
            required: $required,
            schema: $schema,
            style: 'form',
            explode: true,
            example: $example,
        );
    }
}
