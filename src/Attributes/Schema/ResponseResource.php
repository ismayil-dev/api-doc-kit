<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Schema;

use Attribute;
use OpenApi\Attributes\Schema;

#[Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ResponseResource extends Schema
{
    public function __construct(
        private readonly string $entity,
    ) {
        parent::__construct();
    }

    public function getEntity(): string
    {
        return $this->entity;
    }
}
