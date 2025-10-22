<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Schema;

use Attribute;
use OpenApi\Attributes\Schema;

/**
 * Marks a class for automatic OpenAPI schema generation.
 *
 * Apply this attribute to DTOs, value objects, or any typed data class
 * to automatically generate OpenAPI schemas from constructor properties
 * and toArray() method structure.
 *
 * Basic Example:
 * ```php
 * #[DataSchema]
 * class OrderDto implements Arrayable
 * {
 *     public function __construct(
 *         public readonly string $id,
 *         public readonly int $total,
 *     ) {}
 *
 *     public function toArray(): array
 *     {
 *         return [
 *             'id' => $this->id,
 *             'total' => $this->total,
 *         ];
 *     }
 * }
 * ```
 *
 * With Explicit Properties (for computed fields):
 * ```php
 * #[DataSchema(
 *     properties: [
 *         new StringProperty(
 *             property: 'formatted_total',
 *             description: 'Formatted total amount',
 *             example: '$99.99'
 *         ),
 *     ]
 * )]
 * class OrderDto implements Arrayable
 * {
 *     public function toArray(): array
 *     {
 *         return [
 *             'id' => $this->id,
 *             'total' => $this->total,
 *             'formatted_total' => '$' . ($this->total / 100), // Explicitly defined
 *         ];
 *     }
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_CLASS)]
class DataSchema extends Schema
{
    /**
     * @var array<\OpenApi\Attributes\Property>|null Internal storage for explicit property definitions
     */
    private ?array $_explicitProperties = null;

    /**
     * @param  array<\OpenApi\Attributes\Property>|null  $properties  Explicit property definitions for computed fields
     */
    public function __construct(
        ?string $title = null,
        ?string $description = null,
        ?array $properties = null,
    ) {
        parent::__construct(
            title: $title,
            description: $description,
        );

        $this->_explicitProperties = $properties;
    }

    /**
     * Get explicit property definitions
     *
     * @return array<\OpenApi\Attributes\Property>|null
     */
    public function getExplicitProperties(): ?array
    {
        return $this->_explicitProperties;
    }
}
