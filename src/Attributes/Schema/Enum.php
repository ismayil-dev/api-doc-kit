<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Schema;

use Attribute;
use OpenApi\Attributes\Schema;

/**
 * Marks a PHP enum for OpenAPI schema generation.
 *
 * This attribute automatically generates proper OpenAPI enum schemas:
 * - For string-backed enums: Standard enum schema (already works correctly)
 * - For int-backed enums: Adds x-enum-varnames extension for proper SDK generation
 *
 * Example:
 * ```php
 * #[Enum]
 * enum OrderStatus: int
 * {
 *     case Pending = 0;
 *     case Paid = 1;
 *     case Refunded = 2;
 * }
 * ```
 *
 * Generated OpenAPI:
 * ```yaml
 * OrderStatus:
 *   type: integer
 *   enum: [0, 1, 2]
 *   x-enum-varnames: ['Pending', 'Paid', 'Refunded']
 * ```
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Enum extends Schema
{
    public function __construct(
        ?string $title = null,
        ?string $description = null,
    ) {
        parent::__construct(
            title: $title,
            description: $description,
        );
    }
}
