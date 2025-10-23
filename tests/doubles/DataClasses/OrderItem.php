<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Doubles\DataClasses;

use IsmayilDev\ApiDocKit\Attributes\Schema\DataSchema;

/**
 * Test class for array items
 */
#[DataSchema]
class OrderItem
{
    public function __construct(
        public readonly string $sku,
        public readonly int $quantity,
        public readonly int $price,
    ) {}
}
