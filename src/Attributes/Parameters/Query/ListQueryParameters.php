<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Parameters\Query;

use Attribute;

/**
 * Marker attribute placed on a controller method to auto-derive list-query
 * OpenAPI parameters from a project-side definition.
 *
 * The `contributor` class must implement
 * {@see \IsmayilDev\ApiDocKit\Contracts\ListQueryParametersContributor}.
 *
 * Optional `arguments` (associative array) are passed to the contributor's
 * constructor as named arguments — typically a `definition` FQN identifying
 * the list endpoint's filter schema.
 *
 * Example:
 * ```php
 * #[ApiEndpoint(entity: 'Service', responseEntity: 'ServiceData')]
 * #[ListQueryParameters(
 *     contributor: DefinitionParametersContributor::class,
 *     arguments: ['definition' => ServiceListDefinition::class],
 * )]
 * public function __invoke(...) {}
 * ```
 *
 * The route parameter builder picks this up at doc-gen time and merges the
 * contributor's parameters into the operation.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class ListQueryParameters
{
    /**
     * @param  class-string<\IsmayilDev\ApiDocKit\Contracts\ListQueryParametersContributor>  $contributor
     * @param  array<string, mixed>  $arguments  Named arguments forwarded to the contributor's constructor
     */
    public function __construct(
        public readonly string $contributor,
        public readonly array $arguments = [],
    ) {}
}
