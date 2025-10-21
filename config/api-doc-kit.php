<?php

declare(strict_types=1);

return [
    'paths' => [
        'app',
    ],

    'routes' => [
        'parameter_overrides' => [],
    ],

    /**
     * Response schema overrides
     */
    'responses' => [
        /**
         * Success response overrides
         * Set to a custom MediaType class or Schema class to override the default structure
         */
        'success' => [
            'single' => null,        // Override for SingleResourceResponse
            'collection' => null,    // Override for CollectionResponse
            'paginated' => null,     // Override for PaginatedResponse
            'created' => null,       // Override for CreatedResponse
            'updated' => null,       // Override for UpdatedResponse
            'empty' => null,         // Override for EmptyResponse
        ],

        /**
         * Error response overrides
         */
        'error' => [
            /**
             * Default error status codes per HTTP method
             * Override the package defaults globally
             * Use Illuminate\Http\Response constants for status codes
             * Set to null to use package defaults, or provide an array of status codes
             *
             * Package defaults:
             * GET: 401, 403, 404, 429, 500
             * POST: 400, 401, 403, 422, 429, 500
             * PATCH/PUT: 400, 401, 403, 404, 422, 429, 500
             * DELETE: 401, 403, 404, 429, 500
             */
            'defaults_per_method' => [
                'GET' => null,
                'POST' => null,
                'PATCH' => null,
                'PUT' => null,
                'DELETE' => null,
                // Example override:
                // 'GET' => [
                //     Response::HTTP_UNAUTHORIZED,
                //     Response::HTTP_FORBIDDEN,
                //     Response::HTTP_NOT_FOUND,
                // ],
            ],

            /**
             * Global error schema override
             * Set to a custom MediaType class to override the default error structure
             * Example: \App\Http\Responses\CustomErrorContent::class
             */
            'schema' => null,

            /**
             * Per-status-code error schema overrides
             * Define custom schemas for specific error status codes
             * Use Illuminate\Http\Response constants for status codes
             * Example:
             * Response::HTTP_BAD_REQUEST => \App\Http\Responses\BadRequestContent::class,
             * Response::HTTP_UNPROCESSABLE_ENTITY => \App\Http\Responses\ValidationErrorContent::class,
             */
            'per_status' => [
                // Response::HTTP_BAD_REQUEST => null,
                // Response::HTTP_UNPROCESSABLE_ENTITY => null,
            ],

            /**
             * Per-status-code error descriptions
             * Customize the description text for each error response
             * Use Illuminate\Http\Response constants for status codes
             */
            'descriptions' => [
                // Response::HTTP_BAD_REQUEST => 'Custom bad request description',
                // Response::HTTP_UNAUTHORIZED => 'Custom unauthorized description',
                // Response::HTTP_FORBIDDEN => 'Custom forbidden description',
                // Response::HTTP_NOT_FOUND => 'Custom not found description',
                // Response::HTTP_UNPROCESSABLE_ENTITY => 'Custom validation failed description',
                // Response::HTTP_TOO_MANY_REQUESTS => 'Custom rate limit description',
                // Response::HTTP_INTERNAL_SERVER_ERROR => 'Custom server error description',
            ],
        ],
    ],

    'headers' => [

    ],
];
