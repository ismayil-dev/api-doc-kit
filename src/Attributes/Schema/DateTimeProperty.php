<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Schema;

/**
 * Defines date/time formatting for specific properties in DataSchema
 *
 * Used inside #[DataSchema(properties: [...])] to override date/time formatting
 * for specific Carbon/DateTime properties.
 *
 * Example:
 *   #[DataSchema(properties: [
 *       new DateTimeProperty(property: 'createdAt', type: 'datetime'),
 *       new DateTimeProperty(property: 'birthDate', format: 'Y-m-d'),
 *   ])]
 */
final readonly class DateTimeProperty
{
    /**
     * @param  string  $property  The name of the property to configure
     * @param  string|null  $type  Semantic type: 'date', 'time', or 'datetime' (maps to config)
     * @param  string|null  $format  Literal PHP date format string (e.g., 'Y-m-d H:i:s')
     */
    public function __construct(
        public string $property,
        public ?string $type = null,
        public ?string $format = null,
    ) {}
}
