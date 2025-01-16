<?php

namespace IsmayilDev\LaravelDocKit\Attributes\Resources;

use Attribute;
use IsmayilDev\LaravelDocKit\Attributes\Responses\SuccessResponse;
use OpenApi\Attributes\Post;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class PostResource extends Post
{
    public function __construct(
        ?string $path = null,
        ?string $summary = null,
        ?string $description = null,
        ?string $operationId = null,

    ) {
        parent::__construct(
            path: $path,
            operationId: $operationId,
            description: $description,
            summary: $summary,
            responses: [
                new SuccessResponse(),
            ]
        );
    }
}