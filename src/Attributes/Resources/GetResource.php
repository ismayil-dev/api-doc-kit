<?php

namespace IsmayilDev\LaravelDocKit\Attributes\Resources;

use Attribute;
use IsmayilDev\LaravelDocKit\Attributes\Enums\OpenApiPropertyType;
use IsmayilDev\LaravelDocKit\Attributes\Responses\SuccessResponse;
use IsmayilDev\LaravelDocKit\Traits\ResourceTrait;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class GetResource extends Get
{
    use ResourceTrait;

    public function __construct(
        protected string $model,
        ?string $path = null,
        ?array $tags = null,
    ) {
        $entity = $this->getEntity();

        $parameters = [
            new Parameter(
                name: 'id',
                description: "{$entity->name()} id",
                in: 'path',
                required: true,
                schema: new Schema(type: OpenApiPropertyType::STRING->value),
                example: $entity->exampleId()
            ),
        ];

        parent::__construct(
            operationId: $entity->operationId('get'),
            description: $entity->description('Get'),
            summary: $entity->summary('Get'),
            tags: $tags ?? $entity->tags(),
            parameters: $parameters,
            responses: [new SuccessResponse]
        );
    }
}
