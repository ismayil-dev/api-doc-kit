<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Responses;

use Attribute;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Schema;

#[Attribute(Attribute::TARGET_CLASS)]
class JsonCollectionContent extends MediaType
{
    public function __construct(string $ref)
    {
        parent::__construct(
            mediaType: 'application/json',
            schema: new Schema(description: 'Collection of items', type: 'array', items: new Items(ref: $ref))
        );
    }
}
