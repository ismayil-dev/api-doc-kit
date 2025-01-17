<?php

namespace IsmayilDev\LaravelDocKit\Attributes\Resources;

use Attribute;
use OpenApi\Attributes\Delete;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class DeleteResource extends Delete
{
    public function __construct(
        string $entity,
        ?string $description = null,
        ?string $requestClass = null,
        ?string $path = null,
        array $tags = [],
        ?string $operationId = null,
    ) {
        parent::__construct(
            path: $path ?? $entity->value,
            operationId: $operationId ?? $entity->value,
            description: $description ?? $entity->value,
            tags: $tags,
        );
    }
}
