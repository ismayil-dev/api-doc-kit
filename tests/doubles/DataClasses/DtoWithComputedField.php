<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Doubles\DataClasses;

use Illuminate\Contracts\Support\Arrayable;

/**
 * DTO with computed field for testing strict mode
 */
class DtoWithComputedField implements Arrayable
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
            'formatted_total' => '$'.number_format($this->total / 100, 2), // Computed field
        ];
    }
}
