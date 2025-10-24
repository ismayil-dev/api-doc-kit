<?php

declare(strict_types=1);

return [
    'paths' => [
        'app',
    ],

    'routes' => [
        'parameter_overrides' => [],

        /**
         * Route file filtering
         *
         * Define which route files to scan for API documentation
         * By default, only 'api.php' is scanned
         *
         * Examples:
         * - ['api.php'] - Only API routes (default)
         * - ['api.php', 'web.php'] - Both API and web routes
         * - ['api.php', 'api-v2.php'] - Multiple API versions
         */
        'files' => ['api.php'],

        /**
         * Route path exclusion patterns
         *
         * Routes matching these regex patterns will be excluded from documentation
         * Useful for excluding system routes, admin panels, or internal endpoints
         *
         * Common Laravel system routes are excluded by default:
         * - Sanctum CSRF cookie endpoint
         * - Ignition debug screens
         * - Livewire endpoints
         * - Telescope monitoring
         * - Horizon queue dashboard
         * - Laravel Debugbar
         */
        'exclude_paths' => [
            '^sanctum/',
            '^_ignition/',
            '^livewire/',
            '^telescope',
            '^horizon',
            '^_debugbar',
            // Add your own exclusions:
            // '^admin/',
            // '^internal/',
        ],

        /**
         * Skip routes without controllers
         *
         * When enabled, routes that don't have a controller (closure routes, etc.)
         * will be automatically skipped during documentation generation
         *
         * This prevents errors when Laravel's system routes (health checks, etc.)
         * don't have traditional controllers
         */
        'skip_controller_less' => true,
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

        'default_response_suffix' => null,
    ],

    'headers' => [

    ],

    /**
     * Documentation coverage settings
     */
    'documentation' => [
        /**
         * Route patterns to exclude from #[ApiEndpoint] validation
         *
         * Routes matching these regex patterns will not require #[ApiEndpoint] attribute
         * even when strict mode is enabled.
         *
         * Common exclusions (automatically applied):
         * - Laravel Debugbar: ^_debugbar
         * - Laravel Telescope: ^telescope
         * - Laravel Horizon: ^horizon
         * - Health checks: ^(up|health)$
         *
         * Add your own patterns for internal/admin routes:
         */
        'exclude_patterns' => [
            // '^admin/',      // Admin routes
            // '^internal/',   // Internal API routes
            // '^webhook/',    // Webhook handlers (if not documented)
        ],
    ],

    /**
     * Schema generation settings
     */
    'schema' => [
        /**
         * Strict mode for DataSchema attribute
         *
         * When enabled, computed fields in toArray() that cannot be auto-detected
         * must be explicitly defined using property attributes (IntProperty, StringProperty, etc.)
         *
         * When disabled (default), unknown computed fields will default to 'string' type
         * with a warning logged.
         *
         * Example with explicit properties:
         * #[DataSchema(
         *     properties: [
         *         new StringProperty(property: 'formatted_total', description: 'Formatted amount', example: '$99.99'),
         *     ]
         * )]
         *
         * Recommended: Enable strict mode in production to ensure accurate documentation
         */
        'strict_mode' => false,

        /**
         * Date/Time format settings
         *
         * Define default formats for Carbon/DateTime properties in DataSchema classes
         * These can be overridden at the class level or property level using DateTime attributes
         *
         * Formats should use PHP date format characters (e.g., Y-m-d, H:i:s)
         * See: https://www.php.net/manual/en/datetime.format.php
         */
        'date_formats' => [
            'date' => 'Y-m-d',           // Date only (e.g., 2024-01-15)
            'time' => 'H:i:s',           // Time only (e.g., 14:30:00)
            'datetime' => 'Y-m-d H:i:s', // Date and time (e.g., 2024-01-15 14:30:00)
        ],
    ],
];
