<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Responses;

use Attribute;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Schema;

#[Attribute(Attribute::TARGET_CLASS)]
class JsonRefContent extends MediaType
{
    public function __construct(?string $ref = null)
    {
        parent::__construct(
            mediaType: 'application/json',
            schema: $ref ? new Schema(ref: $ref) : null
        );
    }
}
