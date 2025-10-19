<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Responses;

use Attribute;
use IsmayilDev\ApiDocKit\Attributes\Properties\IntProperty;
use IsmayilDev\ApiDocKit\Enums\OpenApiPropertyType;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Attribute(Attribute::TARGET_CLASS)]
class JsonErrorContent extends MediaType
{
    public function __construct(?Schema $schema = null)
    {
        parent::__construct(
            mediaType: 'application/json',
            schema: $schema ?? new Schema(
                title: 'Error',
                description: 'Error schema',
                required: ['statusCode', 'messages'],
                properties: [
                    new IntProperty(description: 'Status Code', property: 'statusCode'),
                    new Property(
                        property: 'messages',
                        type: OpenApiPropertyType::ARRAY->value,
                        items: new Items(type: 'string'),
                    ),
                    new Property(
                        property: 'exception',
                        description: 'Exception (only visible in debug mode)',
                        type: OpenApiPropertyType::OBJECT->value
                    ),
                ],
            )
        );
    }
}
