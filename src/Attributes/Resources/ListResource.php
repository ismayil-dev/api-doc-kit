<?php

namespace IsmayilDev\LaravelDocKit\Attributes\Resources;

use Attribute;
use OpenApi\Attributes\Get;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class ListResource extends Get
{
    public function __construct(
        string $entity,
        ?string $requestClass = null,
        ?string $path = null,
        ?string $summary = null,
        ?string $description = null,
        ?string $operationId = null,
        array $tags = [],
    ) {
        parent::__construct(
            path: $path ?? $entity->value,
            operationId: $operationId ?? $entity->value,
            description: $description ?? $entity->value,
            tags: $tags,
        );
    }
}
