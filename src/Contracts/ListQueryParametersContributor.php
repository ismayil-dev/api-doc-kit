<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Contracts;

use OpenApi\Attributes\Parameter;

/**
 * Contract for classes that contribute query parameters to an OpenAPI operation
 * based on project-specific list/filter definitions.
 *
 * Implementations live in the consuming project (api-doc-kit ships only the
 * interface and the marker attribute that references the contributor FQN).
 *
 * The runtime contract:
 *  - Implementations MUST be no-arg constructible.
 *  - `toOpenApiParameters()` MUST return swagger-php Parameter instances.
 *  - It MUST be deterministic — the same definition must always emit the same
 *    set of parameters, so doc generation is reproducible.
 */
interface ListQueryParametersContributor
{
    /**
     * @return list<Parameter>
     */
    public function toOpenApiParameters(): array;
}
