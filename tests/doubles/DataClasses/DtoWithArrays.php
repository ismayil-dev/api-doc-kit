<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Doubles\DataClasses;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use IsmayilDev\ApiDocKit\Attributes\Properties\ArrayOf;
use IsmayilDev\ApiDocKit\Attributes\Schema\ArrayProperty;
use IsmayilDev\ApiDocKit\Attributes\Schema\DataSchema;

/**
 * Test DTO with various array types
 *
 * Tests different priority levels:
 * - Property-level attribute (highest priority)
 * - DataSchema properties parameter
 * - Default behavior (tested separately based on strict mode)
 */
#[DataSchema(properties: [
    new ArrayProperty(property: 'categories', itemType: 'string'),
])]
class DtoWithArrays implements Arrayable
{
    public function __construct(
        public readonly int $id,
        #[ArrayOf(OrderItem::class)]
        public readonly array $items,
        #[ArrayOf('integer')]
        public readonly array|Collection $ids,
        public readonly array $categories,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'items' => array_map(fn ($item) => [
                'sku' => $item->sku,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ], $this->items),
            'ids' => $this->ids instanceof Collection ? $this->ids->toArray() : $this->ids,
            'categories' => $this->categories,
        ];
    }
}
