<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Resources;

use Attribute;
use IsmayilDev\ApiDocKit\Traits\ResourceTrait;
use OpenApi\Annotations\Operation;

#[Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ApiEndpoint extends Operation
{
    use ResourceTrait;

    public function __construct(
        private readonly string $entity,
        private readonly ?string $requestClass = null,
        private readonly ?string $actionName = null,
        private readonly bool $isList = false,
        ?string $path = null,
        ?string $summary = null,
        ?string $description = null,
        ?string $operationId = null,
        array $parameters = [],
        array $tags = [],
    ) {
        parent::__construct([]);
    }
}
