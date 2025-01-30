<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Routes;

use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyType;

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
