<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Doubles\DataClasses;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Simple DTO for testing constructor property parsing
 */
class SimpleDto implements Arrayable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $age,
        public readonly bool $isActive,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'age' => $this->age,
            'is_active' => $this->isActive,
        ];
    }
}
