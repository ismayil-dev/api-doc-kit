<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Properties;

use Attribute;

/**
 * Defines the item type for array or Collection properties in DataSchema classes
 *
 * Supports both primitive types and object references:
 * - Primitives: 'string', 'integer', 'number', 'boolean'
 * - Objects: SomeClass::class (must have #[DataSchema] attribute)
 *
 * Examples:
 *   #[ArrayOf('string')]           // Array of strings
 *   #[ArrayOf('integer')]          // Array of integers
 *   #[ArrayOf(OrderItem::class)]   // Array of OrderItem objects
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class ArrayOf
{
    /**
     * @param  class-string|string  $itemType  The type of items in the array (class name or primitive type)
     */
    public function __construct(
        public string $itemType,
    ) {}
}
