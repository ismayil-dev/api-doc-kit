<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Doubles\DataClasses;

use Illuminate\Contracts\Support\Arrayable;
use IsmayilDev\ApiDocKit\Attributes\Properties\IntProperty;
use IsmayilDev\ApiDocKit\Attributes\Properties\StringProperty;
use IsmayilDev\ApiDocKit\Attributes\Schema\DataSchema;

/**
 * DTO with explicit property definitions for computed field
 */
#[DataSchema(
    properties: [
        new StringProperty(
            property: 'formatted_total',
            description: 'Formatted total amount',
            example: '$99.99'
        ),
        new IntProperty(
            property: 'computed_value',
            description: 'Computed integer value',
            example: '42'
        ),
    ]
)]
class DtoWithExplicitProperties implements Arrayable
{
    public function __construct(
        public readonly string $id,
        public readonly int $total,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'total' => $this->total,
            'formatted_total' => '$'.number_format($this->total / 100, 2),
            'computed_value' => $this->total * 2,
        ];
    }
}
