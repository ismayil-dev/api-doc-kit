<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Responses;

use Attribute;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Attribute(Attribute::TARGET_CLASS)]
class JsonPaginatedContent extends MediaType
{
    public function __construct(string $ref)
    {
        parent::__construct(
            mediaType: 'application/json',
            schema: new Schema(
                required: ['data', 'pagination'],
                properties: [
                    new Property(
                        property: 'data',
                        description: 'Data',
                        type: 'array',
                        items: new Items(ref: $ref)
                    ),
                    new Property(
                        property: 'pagination',
                        description: 'Pagination',
                        type: 'object',
                        allOf: [new Schema(
                            title: 'Pagination',
                            description: 'Pagination schema',
                            required: ['total', 'count', 'perPage', 'currentPage', 'totalPages'],
                            properties: [
                                new Property(
                                    property: 'total',
                                    description: 'Total number of items',
                                    type: 'integer',
                                    example: 15,
                                ),
                                new Property(
                                    property: 'count',
                                    description: 'Number of items on this page',
                                    type: 'integer',
                                    example: 10,
                                ),
                                new Property(
                                    property: 'perPage',
                                    description: 'Number of items per page',
                                    type: 'integer',
                                    example: 10,
                                ),
                                new Property(
                                    property: 'currentPage',
                                    description: 'Current page number',
                                    type: 'integer',
                                    example: 1,
                                ),
                                new Property(
                                    property: 'totalPages',
                                    description: 'Total number of pages',
                                    type: 'integer',
                                    example: 2,
                                ),
                            ],
                        )]
                    ),
                ],
            )
        );
    }
}
