<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Tests\Doubles\DataClasses;

/**
 * DTO without toArray for testing constructor-only parsing
 */
class MinimalDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
    ) {}
}
