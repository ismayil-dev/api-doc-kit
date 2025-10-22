<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Doubles\DataClasses;

use Illuminate\Contracts\Support\Arrayable;
use IsmayilDev\ApiDocKit\Attributes\Schema\DataSchema;
use IsmayilDev\ApiDocKit\Tests\Doubles\Enums\OrderStatus;

#[DataSchema]
class DtoWithEnum implements Arrayable
{
    public function __construct(
        public readonly int $id,
        public readonly OrderStatus $status,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
        ];
    }
}
