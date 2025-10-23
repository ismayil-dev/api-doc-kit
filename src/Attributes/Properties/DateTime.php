<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Properties;

use Attribute;

/**
 * Defines date/time formatting for Carbon/DateTime properties in DataSchema classes
 *
 * Supports both semantic types (date, time, datetime) and literal format strings.
 * When both type and format are specified, format takes precedence.
 *
 * Examples:
 *   #[DateTime(type: 'date')]                    // Uses config format for 'date'
 *   #[DateTime(format: 'Y-m-d')]                 // Uses literal format
 *   #[DateTime(type: 'date', format: 'd/m/Y')]   // Override config format
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class DateTime
{
    /**
     * @param  string|null  $type  Semantic type: 'date', 'time', or 'datetime' (maps to config)
     * @param  string|null  $format  Literal PHP date format string (e.g., 'Y-m-d H:i:s')
     */
    public function __construct(
        public ?string $type = null,
        public ?string $format = null,
    ) {}
}
