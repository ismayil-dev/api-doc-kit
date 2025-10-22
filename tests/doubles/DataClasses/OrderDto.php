<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Doubles\DataClasses;

use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

/**
 * DTO with computed fields in toArray() for testing
 */
class OrderDto implements Arrayable
{
    public function __construct(
        public readonly string $id,
        public readonly string $description,
        public readonly int $total,
        public readonly OrderStatus $status,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'total' => $this->total,
            'status' => $this->status->value,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'formatted_total' => '$'.number_format($this->total / 100, 2),
        ];
    }
}
