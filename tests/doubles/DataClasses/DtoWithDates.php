<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Doubles\DataClasses;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use IsmayilDev\ApiDocKit\Attributes\Properties\DateTime;
use IsmayilDev\ApiDocKit\Attributes\Schema\DataSchema;
use IsmayilDev\ApiDocKit\Attributes\Schema\DateTimeProperty;

/**
 * Test DTO with various date/time formats
 *
 * Tests different priority levels:
 * - Property-level attribute (highest priority)
 * - DataSchema properties parameter
 * - Global config (lowest priority, tested separately)
 */
#[DataSchema(properties: [
    new DateTimeProperty(property: 'updated_at', type: 'datetime'),
    new DateTimeProperty(property: 'published_at', format: 'd/m/Y H:i'),
])]
class DtoWithDates implements Arrayable
{
    public function __construct(
        public readonly int $id,
        #[DateTime(format: 'Y-m-d')]
        public readonly Carbon $birthDate,
        #[DateTime(type: 'datetime')]
        public readonly Carbon $createdAt,
        public readonly Carbon $updatedAt,
        public readonly Carbon $publishedAt,
        public readonly Carbon $defaultFormatDate,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'birth_date' => $this->birthDate->format('Y-m-d'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'published_at' => $this->publishedAt->format('d/m/Y H:i'),
            'default_format_date' => $this->defaultFormatDate->format('Y-m-d H:i:s'),
        ];
    }
}
