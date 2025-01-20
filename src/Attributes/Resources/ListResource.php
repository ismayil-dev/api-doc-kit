<?php

namespace IsmayilDev\LaravelDocKit\Attributes\Resources;

use Attribute;
use IsmayilDev\LaravelDocKit\Attributes\Responses\SuccessResponse;
use IsmayilDev\LaravelDocKit\Traits\ResourceTrait;
use OpenApi\Attributes\Get;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class ListResource extends Get
{
    use ResourceTrait;

    public function __construct(
        private readonly string $model,
        private readonly ?string $requestClass = null,
        private readonly ?string $actionName = null,
        ?string $path = null,
        ?string $summary = null,
        ?string $description = null,
        ?string $operationId = null,
        array $tags = [],
    ) {
        parent::__construct(
            path: $path,
            operationId: $operationId,
            description: $description,
            summary: $summary,
            tags: $tags,
            responses: [new SuccessResponse],
        );
    }
}
