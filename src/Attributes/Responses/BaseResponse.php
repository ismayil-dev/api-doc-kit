<?php

namespace IsmayilDev\ApiDocKit\Attributes\Responses;

use Attribute;
use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class BaseResponse extends Response
{
    public function __construct(
        int $statusCode,
        string $description,
        array $additionalProperties = [],
        ?string $ref = null,
    ) {
        $properties = [
            new Property(
                property: 'statusCode',
                description: 'Status Code',
                type: OpenApiPropertyType::NUMBER->value,
                example: $statusCode
            ),
            new Property(
                property: 'messages',
                description: 'List of error messages',
                type: OpenApiPropertyType::ARRAY->value,
                items: new Items(type: 'string'),
                example: $this->getMessages()
            ),
            new Property(
                property: 'exception',
                description: 'Exception',
                type: OpenApiPropertyType::OBJECT->value,
                example: 'Exception'
            ),
            ...$additionalProperties,
        ];

        $requiredFields = collect($properties)->map(fn ($property) => $property->property)->toArray();

        parent::__construct(
            response: $statusCode,
            description: $description,
            content: new JsonContent(
                ref: $ref ?? '#/components/schemas/ErrorResponse',
                title: 'Error',
                description: 'Error schema',
                required: $requiredFields,
                properties: $properties,
            )
        );
    }

    protected function getMessages(): array
    {
        return ['Something went wrong'];
    }
}
