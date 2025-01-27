<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Schema;

use Attribute;
use OpenApi\Annotations\Operation;

#[Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ModelSchema extends Operation
{
    public function __construct()
    {
        parent::__construct([]);
    }
}
