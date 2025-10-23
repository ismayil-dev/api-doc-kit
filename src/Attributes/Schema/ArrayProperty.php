<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Schema;

/**
 * Defines array item type for specific properties in DataSchema
 *
 * Used inside #[DataSchema(properties: [...])] to specify array item types
 * for specific properties.
 *
 * Example:
 *   #[DataSchema(properties: [
 *       new ArrayProperty(property: 'items', itemType: OrderItem::class),
 *       new ArrayProperty(property: 'tags', itemType: 'string'),
 *   ])]
 */
final readonly class ArrayProperty
{
    /**
     * @param  string  $property  The name of the array property to configure
     * @param  class-string|string  $itemType  The type of items in the array (class name or primitive type)
     */
    public function __construct(
        public string $property,
        public string $itemType,
    ) {}
}
