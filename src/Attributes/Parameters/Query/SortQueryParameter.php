<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Parameters\Query;

use Attribute;
use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;

/**
 * Documents a `sort` query parameter that accepts a comma-separated list of
 * field names, each optionally prefixed with `-` for descending order.
 *
 * Example URL: `?sort=-createdAt,name`
 *
 * The schema's `enum` lists every (field, -field) pair so docs/SDKs know
 * the exact set of accepted tokens; clients still pass them comma-joined.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class SortQueryParameter extends Parameter
{
    /**
     * @param  list<string>  $allowedFields  Field names sortable by this endpoint
     */
    public function __construct(
        array $allowedFields,
        string $name = 'sort',
        ?string $description = null,
        bool $required = false,
        string $example = Generator::UNDEFINED,
    ) {
        $tokens = [];
        foreach ($allowedFields as $field) {
            $tokens[] = $field;
            $tokens[] = '-'.$field;
        }

        parent::__construct(
            name: $name,
            description: $description ?? 'Comma-separated list of sort tokens. Prefix with `-` for descending. Allowed: '.implode(', ', $tokens),
            in: 'query',
            required: $required,
            schema: new Schema(
                type: OpenApiPropertyType::STRING->value,
                pattern: '^-?[A-Za-z][A-Za-z0-9_.]*(,-?[A-Za-z][A-Za-z0-9_.]*)*$',
            ),
            example: $example,
        );
    }
}
