<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Parameters\Query;

use Attribute;
use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use OpenApi\Generator;

/**
 * Documents a `sort` query parameter that accepts ONE token: a field name
 * optionally prefixed with `-` for descending order.
 *
 * Example URL: `?sort=-createdAt`
 *
 * The schema emits an `enum` listing every (field, -field) pair so:
 *  - generated SDKs get a typed union (`'createdAt' | '-createdAt' | ...`)
 *    instead of an opaque `string`.
 *  - Postman shows a dropdown of allowed values instead of generating
 *    regex-matching gibberish from a `pattern`.
 *
 * Multi-token comma-separated sort (`?sort=-createdAt,name`) is still parsed
 * by consumers if they choose to support it at runtime, but it is intentionally
 * NOT documented in the OpenAPI spec — single-token sort covers the vast
 * majority of real use cases and produces a much better SDK / Postman
 * experience.
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

        // Default the example to the first descending-sort token if the
        // caller didn't pass one — gives Postman a non-empty placeholder
        // that's actually meaningful.
        if ($example === Generator::UNDEFINED && $tokens !== []) {
            $example = $tokens[1] ?? $tokens[0];
        }

        parent::__construct(
            name: $name,
            description: $description ?? 'Sort token. Prefix the field name with `-` for descending order. Allowed: '.implode(', ', $tokens),
            in: 'query',
            required: $required,
            schema: new Schema(
                type: OpenApiPropertyType::STRING->value,
                enum: $tokens,
            ),
            example: $example,
        );
    }
}
