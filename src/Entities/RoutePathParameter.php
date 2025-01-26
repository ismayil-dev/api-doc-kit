<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Entities;

use IsmayilDev\ApiDocKit\Attributes\Enums\OpenApiPropertyType;

class RoutePathParameter
{
    public function __construct(
        public string $name,
        public OpenApiPropertyType $type,
        public string $description,
        public string|int|null $example,
        public bool $optional = false,
    ) {}
}
