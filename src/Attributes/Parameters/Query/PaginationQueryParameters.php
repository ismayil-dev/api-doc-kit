<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Attributes\Parameters\Query;

use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;

/**
 * Composite helper that emits the standard pagination query parameters
 * (`page` and `per_page`).
 *
 * Spread its result into an `ApiEndpoint`'s `parameters` argument:
 *
 * ```php
 * #[ApiEndpoint(
 *     entity: 'Service',
 *     parameters: [
 *         ...PaginationQueryParameters::make(defaultPerPage: 15, maxPerPage: 100),
 *     ],
 * )]
 * ```
 */
final class PaginationQueryParameters
{
    /**
     * @return list<Parameter>
     */
    public static function make(
        int $defaultPerPage = 15,
        int $maxPerPage = 100,
        string $pageName = 'page',
        string $perPageName = 'per_page',
    ): array {
        return [
            new Parameter(
                name: $pageName,
                description: 'Page number (1-indexed)',
                in: 'query',
                required: false,
                schema: new Schema(
                    type: 'integer',
                    minimum: 1,
                    default: 1,
                ),
            ),
            new Parameter(
                name: $perPageName,
                description: 'Items per page',
                in: 'query',
                required: false,
                schema: new Schema(
                    type: 'integer',
                    minimum: 1,
                    maximum: $maxPerPage,
                    default: $defaultPerPage,
                ),
            ),
        ];
    }
}
